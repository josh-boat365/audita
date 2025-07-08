<!DOCTYPE html>
<html>

<head>
    <title>Exceptions Report - PDF Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .summary {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            padding: 15px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Exceptions Report</h1>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <div class="summary">
        <h2>Executive Summary</h2>
        <p><strong>Total Exceptions:</strong> {{ count($data) }}</p>
        @if ($filters['batch'])
            <p><strong>Batch:</strong> {{ $filters['batch'] }}</p>
        @endif
        @if ($filters['branch'])
            <p><strong>Branch:</strong> {{ $filters['branch'] }}</p>
        @endif
        @if ($filters['status'])
            <p><strong>Status:</strong> {{ $filters['status'] }}</p>
        @endif
        @if ($filters['dateFrom'] || $filters['dateTo'])
            <p><strong>Date Range:</strong>
                {{ $filters['dateFrom'] ?? 'Start' }} to {{ $filters['dateTo'] ?? 'End' }}
            </p>
        @endif
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Exception Title</th>
                <th>Exception</th>
                <th>Root Cause</th>
                <th>Auditor</th>
                <th>Auditee</th>
                <th>Process Type</th>
                <th>Sub Process Type</th>
                <th>Department</th>
                <th>Status</th>
                <th>Occurrence Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $report)
                <tr>
                    <td>{{ $report['exceptionTitle'] ?? 'N/A' }}</td>
                    <td>{{ $report['exception'] ?? 'N/A' }}</td>
                    <td>{{ $report['rootCause'] ?? 'N/A' }}</td>
                    <td>{{ $report['auditorName'] ?? 'N/A' }}</td>
                    <td>{{ $report['auditeeName'] ?? 'N/A' }}</td>
                    <td>{{ $report['processType'] ?? 'N/A' }}</td>
                    <td>{{ $report['subProcessType'] ?? 'N/A' }}</td>
                    <td>{{ $report['department'] ?? 'N/A' }}</td>
                    <td>{{ $report['status'] ?? 'N/A' }}</td>
                    <td>{{ $report['occurrenceDate'] ? \Carbon\Carbon::parse($report['occurrenceDate'])->format('Y-m-d') : 'N/A' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Confidential - For Internal Use Only</p>
    </div>
</body>

</html>
