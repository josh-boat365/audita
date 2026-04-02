# Auditor Report Analysis

## 📋 Overview
The auditor-report page is a comprehensive report generation and filtering interface that allows auditors to analyze exceptions by various criteria and export filtered reports to Word documents.

---

## 🔗 Associated Controllers & Functions

### Primary Controller: `ReportsController.php`

**Main Method:** `auditorReport()`
**Location:** `/app/Http/Controllers/ReportsController.php` (Lines 39-70)

```php
public function auditorReport()
{
    // Validate session
    $sessionValidation = $this->validateSession();
    if ($sessionValidation) {
        return $sessionValidation;
    }

    $reportsData = collect($this->getAllReports());
    $batchData = BatchController::getBatches();
    $employeeData = ExceptionManipulationController::getLoggedInUserInformation();
    
    $employeeFullName = $employeeData->firstName . ' ' . $employeeData->surname;
    $employeeDepartment = $employeeData->department->name;
    
    $batches = collect($batchData)->filter(function ($batch) use ($employeeDepartment) {
        return isset($batch->createdAt) && ($employeeDepartment === $batch->auditorUnitName);
    });
    
    // ⭐ KEY FILTERING LOGIC HERE ⭐
    $statuses = ['APPROVED', 'ANALYSIS', 'RESOLVED'];
    $retrieveExceptions = FilterExceptionController::handleException($reportsData, $statuses);
    $reports = $retrieveExceptions;
    
    $groups = $this->getGroupsForAuditorUnit($reportsData, $batches);
    
    return view('reports.auditor-report', compact('reports', 'batches', 'groups'));
}
```

### Key Method: `getAllReports()`
**Location:** Lines 72-95

```php
public static function getAllReports()
{
    $access_token = session('api_token');
    
    try {
        $response = Http::withToken($access_token)
            ->get('http://192.168.1.200:5126/Auditor/ExceptionTracker');
        
        if ($response->successful()) {
            $Reports = $response->object() ?? [];
        } elseif ($response->status() == 404) {
            $Reports = [];
            Log::warning('Exception Reports API returned 404 Not Found');
        } else {
            $Reports = [];
            Log::error('Exception Reports API request failed', ['status' => $response->status()]);
            return redirect()->route('login')->with('toast_error', 'Error fetching exception Reports data');
        }
    } catch (\Exception $e) {
        $Reports = [];
        Log::error('Error fetching exception Reports', ['error' => $e->getMessage()]);
        return redirect()->back()->with('toast_error', 'Something went wrong...');
    }
    return $Reports;
}
```

**External API Call:**
```
GET http://192.168.1.200:5126/Auditor/ExceptionTracker
```

### Filter Exception Controller: `FilterExceptionController.php`

**Method:** `handleException()`
**Location:** `/app/Http/Controllers/FilterExceptionController.php` (Lines 11-60)

```php
public static function handleException(object $data, array $statuses)
{
    $exceptionData = $data;
    $batches = BatchController::getBatches();
    $groups = GroupController::getActivityGroups();
    $groupMembers = GroupMembersController::getGroupMembers();
    $employeeId = ExceptionManipulationController::getLoggedInUserInformation()->id;
    
    // Filter active batches with status 'OPEN' and map them by ID
    $validBatches = collect($batches)
        ->filter(fn($batch) => $batch->active && $batch->status === 'OPEN')
        ->keyBy('id');
    
    // Filter active groups and map them by ID
    $validGroups = collect($groups)
        ->filter(fn($group) => $group->active)
        ->keyBy('id');
    
    // Get groups where the specified employee belongs
    $employeeGroups = collect($groupMembers)
        ->where('employeeId', $employeeId)
        ->pluck('activityGroupId')
        ->unique();
    
    $employeeRoleId = ExceptionManipulationController::getLoggedInUserInformation()->empRoleId;
    
    // Top managers: 1-MD, 2-Head of IA, 4-Head of IC&C
    $topManagers = [1, 2, 4];
    
    // Filter exceptions
    $filteredExceptions = collect($exceptionData)->filter(function ($exception) use ($validBatches, $validGroups, $employeeGroups, $topManagers, $employeeRoleId, $statuses) {
        $groupId = $exception->activityGroupId;
        
        // Check if batch is valid (active and OPEN)
        $hasValidBatch = $validBatches->has($exception->exceptionBatchId);
        
        // Check if group is valid (active)
        $hasValidGroup = $validGroups->has($groupId);
        
        // ⭐ STATUS FILTERING ⭐
        $hasValidStatus = in_array($exception->status, $statuses);
        
        // Check access: employee belongs to group OR is a top manager
        $hasAccess = $employeeGroups->contains($groupId) || in_array($employeeRoleId, $topManagers);
        
        return $hasValidBatch && $hasValidGroup && $hasValidStatus && $hasAccess;
    });
    
    return $filteredExceptions->values()->all();
}
```

---

## 🎯 Answer to Your Question

### **Status Filtering for Auditors**

**❌ NO - Not only RESOLVED and APPROVED**

The auditor can filter by **THREE statuses**, not two:

| Status | Allowed | Notes |
|--------|---------|-------|
| **APPROVED** | ✅ YES | Exceptions approved by supervisor, ready for analysis |
| **ANALYSIS** | ✅ YES | Exceptions currently under auditor analysis |
| **RESOLVED** | ✅ YES | Exceptions that have been resolved |
| **PENDING** | ❌ NO | Excluded from reports |
| **REVIEW** | ❌ NO | Excluded from reports |
| **AMENDMENT** | ❌ NO | Excluded from reports |
| **DECLINED** | ❌ NO | Explicitly excluded from reports |

### How This Works

**In ReportsController.php (Line 63):**
```php
$statuses = ['APPROVED', 'ANALYSIS', 'RESOLVED'];
$retrieveExceptions = FilterExceptionController::handleException($reportsData, $statuses);
```

**The statuses array is passed to FilterExceptionController::handleException()** which filters all reports to ONLY include exceptions with these three statuses:

```php
$hasValidStatus = in_array($exception->status, $statuses);
```

---

## 📊 Data Flow for Auditor Report

```
┌─────────────────────────┐
│  auditorReport()        │
│  Request Initiated      │
└────────┬────────────────┘
         │
         ├─► getAllReports()
         │   └─► External API: GET /Auditor/ExceptionTracker
         │       Returns: ALL exceptions (all statuses)
         │
         ├─► Define allowed statuses
         │   └─► ['APPROVED', 'ANALYSIS', 'RESOLVED']
         │
         └─► FilterExceptionController::handleException()
             Filter Logic Applied:
             ✓ Active batches with status 'OPEN'
             ✓ Active groups
             ✓ Status in ['APPROVED', 'ANALYSIS', 'RESOLVED']
             ✓ User has access (belongs to group OR is top manager)
             
             Returns: PRE-FILTERED reports data
             
         └─► View: reports.auditor-report
             └─► Status Dropdown Populated From:
                 array_unique(array_column($reports, 'status'))
                 
                 Only shows: APPROVED, ANALYSIS, RESOLVED
                 (since ALL other statuses are already filtered out)
```

---

## 🎨 Filter UI Options

**File:** `resources/views/reports/auditor-report.blade.php` (Lines 28-66)

The status filter dropdown is dynamically populated:

```blade
<div class="col-md-2">
    <label for="statusFilter" class="form-label">Status</label>
    <select id="statusFilter" class="form-select">
        <option value="">All Statuses</option>
        @foreach (array_unique(array_column($reports, 'status')) as $status)
            @if ($status)
                <option value="{{ $status }}">{{ $status }}</option>
            @endif
        @endforeach
    </select>
</div>
```

Since `$reports` is pre-filtered to only include **APPROVED, ANALYSIS, RESOLVED**, the dropdown will only show these options.

---

## 🔗 Route Definition

**File:** `routes/web.php` (Line 251)

```php
Route::get('/auditor-reports', [ReportsController::class, 'auditorReport'])
    ->name('auditor.report');
```

---

## 📋 Available Filters in Auditor Report

All filterable fields:

| Filter | Type | Options Source | Notes |
|--------|------|-----------------|-------|
| **Branch** | Required | Unique branches from grouped reports | Shows branch names where auditor has exceptions |
| **Auditor** | Optional | Auditors in selected branch | Dynamically populated after branch selection |
| **Status** | Optional | APPROVED, ANALYSIS, RESOLVED | Pre-filtered in controller |
| **Risk Rate** | Optional | Unique risk rates in data | High, Medium, Low, etc. |
| **Process Type** | Optional | Unique process types | Pre-calculated in data |
| **Department** | Optional | Unique departments | Pre-calculated in data |
| **Exception Batch** | Optional | Unique batches | Pre-calculated in data |
| **From Date** | Optional | Date picker | Filters by occurrence date |
| **To Date** | Optional | Date picker | Filters by occurrence date |

---

## 💾 Export Functionality

The report can be exported to **Word Document** (.docx) format with:
- Report title (dynamically generated)
- Executive summary (auto-generated from filtered data)
- Exception details (detailed breakdown)
- Applied filters summary
- Risk distribution analysis

**JavaScript Function:** `downloadWordDocument()` (Line 946+)

---

## 🔐 Access Control

| Role Type | Access |
|-----------|--------|
| **Auditor (Department 7, 8)** | ✅ Full access - can view only own department's reports |
| **Top Managers (1, 2, 4)** | ✅ Full access - can view all reports |
| **Others** | ❌ No reports visible |

Access is controlled by:
1. Session validation
2. Employee's department
3. Batch association with auditor unit
4. Group membership

---

## 🚀 Usage Flow

1. **User navigates to** `/auditor-reports`
2. **Controller filters data** to only APPROVED, ANALYSIS, RESOLVED statuses
3. **View displays pre-filtered data**
4. **User selects filters** from dropdown (all options are pre-filtered)
5. **JavaScript applies client-side filtering** on the already-filtered data
6. **User can download report** as Word document

---

## 📌 Key Takeaways

✅ **Auditors can only generate reports for THREE statuses:**
- APPROVED (approved by supervisor)
- ANALYSIS (in auditor analysis phase)
- RESOLVED (completed)

❌ **Cannot filter by:**
- PENDING (awaiting review)
- REVIEW (under review)
- AMENDMENT (pending amendments)
- DECLINED (rejected)

This is a **backend restriction** in `ReportsController::auditorReport()` method (Line 63), not just a UI restriction. The API returns all exceptions, but they are filtered server-side before the view receives them.
