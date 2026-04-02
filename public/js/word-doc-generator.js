/**
 * PROFESSIONAL WORD DOCUMENT GENERATOR
 * ======================================
 * Drop-in replacement for the downloadWordDocument() and related functions.
 * Uses docx.js (loaded via CDN) to produce a real .docx file — not HTML-as-.doc.
 *
 * SETUP: Add this CDN script tag BEFORE this script runs:
 *   <script src="https://unpkg.com/docx@8.5.0/build/index.js"></script>
 *
 * Replace the entire "WORD DOCUMENT DOWNLOAD FUNCTION" section in your blade
 * with this file's contents (paste inside the existing <script> tag).
 */

// ============================================
// WORD DOCUMENT DOWNLOAD FUNCTION (REPLACE OLD SECTION)
// ============================================

function handleDownloadReport() {
    downloadWordDocument();
}

async function downloadWordDocument() {
    if (filteredData.length === 0) {
        alert('No data to export. Please select a branch and apply filters first.');
        return;
    }

    if (typeof docx === 'undefined') {
        alert('Document library not loaded. Please refresh the page and try again.');
        return;
    }

    showLoading();

    try {
        const filters = getFilterValues();

        // Try to fetch the logo as base64; silently skip if unavailable
        let logoBase64 = null;
        try {
            const resp  = await fetch('/bpsl_imgs/purple-logo-bpsl.png');
            const blob  = await resp.blob();
            logoBase64  = await blobToBase64(blob);
        } catch (_) { /* logo optional */ }

        const buffer = await buildDocxBuffer(filters, logoBase64);

        const timestamp = new Date().toISOString().slice(0, 10);
        const filename  = `Exception_Report_${filters.branch.replace(/\s+/g, '_')}_${timestamp}.docx`;

        triggerDownload(buffer, filename);
    } catch (err) {
        console.error('Error generating Word document:', err);
        alert('Error generating document. Please try again.\n\n' + err.message);
    } finally {
        hideLoading();
    }
}

// -----------------------------------------------
// Helper: blob → base64 ArrayBuffer
// -----------------------------------------------
function blobToBase64(blob) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onloadend = () => {
            // result is "data:image/png;base64,AAAA…" — strip the prefix
            const b64 = reader.result.split(',')[1];
            // convert to Uint8Array for docx ImageRun
            const binary = atob(b64);
            const bytes  = new Uint8Array(binary.length);
            for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
            resolve(bytes.buffer);
        };
        reader.onerror = reject;
        reader.readAsDataURL(blob);
    });
}

// -----------------------------------------------
// Helper: trigger browser download from ArrayBuffer
// -----------------------------------------------
function triggerDownload(buffer, filename) {
    const blob = new Blob([buffer], {
        type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    });
    const url  = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href     = url;
    link.download = filename;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    setTimeout(() => URL.revokeObjectURL(url), 100);
}

// ============================================
// CORE DOCUMENT BUILDER
// ============================================
async function buildDocxBuffer(filters, logoArrayBuffer) {
    const {
        Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
        ImageRun, Header, Footer, AlignmentType, HeadingLevel,
        BorderStyle, WidthType, ShadingType, VerticalAlign,
        PageNumber, PageBreak, LevelFormat, TabStopType, TabStopPosition,
        UnderlineType
    } = docx;

    // ---- Colours & measures ----
    const NAVY        = '1B2A4A';
    const NAVY_LIGHT  = '2C4066';
    const TEAL        = '0B6477';
    const GOLD        = 'B8952A';
    const GREY_BG     = 'F5F6F8';
    const GREY_RULE   = 'CCCCCC';
    const WHITE       = 'FFFFFF';
    const TEXT_DARK   = '1A1A2E';
    const TEXT_MID    = '444455';

    // Page: A4, 2.5 cm margins (1417 DXA ≈ 1 cm)
    const PAGE_W      = 11906;
    const PAGE_H      = 16838;
    const MARGIN      = 1134; // ~2 cm
    const CONTENT_W   = PAGE_W - MARGIN * 2; // 9638 DXA

    // ---- Shared border helper ----
    const thinBorder  = (colour = GREY_RULE) => ({ style: BorderStyle.SINGLE, size: 4, color: colour });
    const noBorder    = () => ({ style: BorderStyle.NONE, size: 0, color: 'FFFFFF' });
    const allNoBorder = () => ({ top: noBorder(), bottom: noBorder(), left: noBorder(), right: noBorder() });
    const allThin     = (c)  => ({ top: thinBorder(c), bottom: thinBorder(c), left: thinBorder(c), right: thinBorder(c) });

    // ---- Typography helpers ----
    const run = (text, opts = {}) => new TextRun({ text, font: 'Calibri', size: 22, color: TEXT_DARK, ...opts });

    const boldRun = (text, opts = {}) => run(text, { bold: true, ...opts });

    const para = (children, opts = {}) => new Paragraph({
        children: Array.isArray(children) ? children : [children],
        spacing: { after: 120 },
        ...opts,
    });

    const spacer = (pts = 1) => new Paragraph({
        children: [new TextRun('')],
        spacing: { after: pts * 20 },
    });

    // ---- Section heading ----
    const sectionHeading = (text) => new Paragraph({
        children: [
            new TextRun({
                text: text.toUpperCase(),
                bold: true,
                font: 'Calibri',
                size: 24,
                color: WHITE,
            }),
        ],
        shading: { fill: NAVY, type: ShadingType.CLEAR },
        spacing: { before: 320, after: 0 },
        indent: { left: 200, right: 200 },
        border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: GOLD } },
    });

    // ---- Sub-label inside an exception block ----
    const blockLabel = (text, colour = NAVY) => new Paragraph({
        children: [boldRun(text, { size: 20, color: colour })],
        spacing: { before: 160, after: 60 },
    });

    const blockText = (text) => new Paragraph({
        children: [run(text, { size: 20, color: TEXT_MID })],
        spacing: { after: 60 },
        alignment: AlignmentType.JUSTIFIED,
    });

    // ============================================
    // REPORT DATA
    // ============================================
    const reportTitle   = generateReportTitle(filters);
    const execSummary   = stripHtml(generateExecutiveSummary(filteredData, filters));
    const grouped       = groupExceptionsByTitle(filteredData);
    const riskDist      = getRiskDistribution(filteredData);
    const statusInfo    = getStatusAnalysis(filteredData);
    const auditPeriod   = determineAuditPeriod(filters);

    const departments   = [...new Set(filteredData.map(r => r.department))].filter(Boolean);
    const uniqueAuditors = [...new Set(filteredData.map(r => r.auditorName))].filter(Boolean);

    // ============================================
    // HEADER
    // ============================================
    const headerChildren = [
        new Paragraph({
            children: [
                boldRun('BEST POINT SAVINGS AND LOANS LIMITED', { size: 18, color: NAVY }),
                new TextRun('\t'),
                run(new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' }),
                    { size: 16, color: TEXT_MID }),
            ],
            tabStops: [{ type: TabStopType.RIGHT, position: TabStopPosition.MAX }],
            border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: GOLD } },
            spacing: { after: 0 },
        }),
    ];

    if (logoArrayBuffer) {
        headerChildren.unshift(
            new Paragraph({
                children: [
                    new ImageRun({
                        type: 'png',
                        data: logoArrayBuffer,
                        transformation: { width: 120, height: 40 },
                        altText: { title: 'BPSL Logo', description: 'Best Point Logo', name: 'Logo' },
                    }),
                ],
                spacing: { after: 60 },
            })
        );
    }

    // ============================================
    // FOOTER
    // ============================================
    const footerPara = new Paragraph({
        children: [
            run('CONFIDENTIAL — Internal Audit Report  |  Best Point Savings and Loans Ltd  |  Page ', { size: 16, color: TEXT_MID }),
            new TextRun({ children: [PageNumber.CURRENT], font: 'Calibri', size: 16, color: TEXT_MID }),
            run(' of ', { size: 16, color: TEXT_MID }),
            new TextRun({ children: [PageNumber.TOTAL_PAGES], font: 'Calibri', size: 16, color: TEXT_MID }),
        ],
        border: { top: { style: BorderStyle.SINGLE, size: 4, color: GREY_RULE } },
        spacing: { before: 100 },
        alignment: AlignmentType.CENTER,
    });

    // ============================================
    // COVER PAGE
    // ============================================
    const coverChildren = [];

    if (logoArrayBuffer) {
        coverChildren.push(
            spacer(80),
            new Paragraph({
                children: [
                    new ImageRun({
                        type: 'png',
                        data: logoArrayBuffer,
                        transformation: { width: 200, height: 67 },
                        altText: { title: 'BPSL Logo', description: 'Best Point Logo', name: 'Logo' },
                    }),
                ],
                alignment: AlignmentType.CENTER,
            })
        );
    }

    coverChildren.push(
        spacer(120),
        new Paragraph({
            children: [boldRun('INTERNAL AUDIT REPORT', { size: 52, color: NAVY, allCaps: true })],
            alignment: AlignmentType.CENTER,
            spacing: { after: 40 },
        }),
        new Paragraph({
            children: [boldRun(filters.branch.toUpperCase() + ' BRANCH', { size: 36, color: GOLD })],
            alignment: AlignmentType.CENTER,
            spacing: { after: 200 },
        }),
        new Paragraph({
            children: [run(`Audit Period: ${auditPeriod}`, { size: 24, color: TEXT_MID })],
            alignment: AlignmentType.CENTER,
            spacing: { after: 40 },
        }),
        new Paragraph({
            children: [run(`Report Date: ${new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' })}`, { size: 24, color: TEXT_MID })],
            alignment: AlignmentType.CENTER,
            spacing: { after: 40 },
        }),
        new Paragraph({
            children: [run(`Total Exceptions: ${filteredData.length}`, { size: 24, color: TEXT_MID })],
            alignment: AlignmentType.CENTER,
            spacing: { after: 400 },
        }),
        // Decorative rule
        new Paragraph({
            children: [new TextRun('')],
            border: { bottom: { style: BorderStyle.SINGLE, size: 12, color: NAVY } },
            spacing: { after: 200 },
        }),
        new Paragraph({
            children: [run('CONFIDENTIAL', { size: 20, color: GOLD, bold: true, allCaps: true })],
            alignment: AlignmentType.CENTER,
            spacing: { after: 20 },
        }),
        new Paragraph({
            children: [run('This document contains privileged and confidential information intended solely for the use of management. Unauthorised distribution is strictly prohibited.', { size: 18, color: TEXT_MID, italics: true })],
            alignment: AlignmentType.CENTER,
        }),
        // Page break to start body
        new Paragraph({ children: [new PageBreak()] }),
    );

    // ============================================
    // SUMMARY METRICS TABLE
    // ============================================
    const metricCellW = Math.floor(CONTENT_W / 4);

    function metricCell(label, value, bgFill = GREY_BG) {
        return new TableCell({
            borders: allNoBorder(),
            width: { size: metricCellW, type: WidthType.DXA },
            shading: { fill: bgFill, type: ShadingType.CLEAR },
            margins: { top: 120, bottom: 120, left: 160, right: 160 },
            verticalAlign: VerticalAlign.CENTER,
            children: [
                new Paragraph({
                    children: [boldRun(String(value), { size: 48, color: NAVY })],
                    alignment: AlignmentType.CENTER,
                    spacing: { after: 40 },
                }),
                new Paragraph({
                    children: [run(label, { size: 17, color: TEXT_MID })],
                    alignment: AlignmentType.CENTER,
                    spacing: { after: 0 },
                }),
            ],
        });
    }

    const metricsTable = new Table({
        width: { size: CONTENT_W, type: WidthType.DXA },
        columnWidths: [metricCellW, metricCellW, metricCellW, metricCellW],
        rows: [
            new TableRow({
                children: [
                    metricCell('Total Exceptions',  filteredData.length),
                    metricCell('Resolved',          statusInfo.resolvedCount,  'D4EDD9'),
                    metricCell('Pending',           statusInfo.pendingCount,   'FFF3CD'),
                    metricCell('Resolution Rate',   statusInfo.resolutionRate + '%', 'D0E8F0'),
                ],
            }),
        ],
    });

    // ============================================
    // RISK DISTRIBUTION TABLE
    // ============================================
    const riskRows = Object.entries(riskDist).map(([level, count]) => {
        const pct   = ((count / filteredData.length) * 100).toFixed(1);
        const fills = { High: 'F8D7DA', Medium: 'FFF3CD', Low: 'D4EDD9' };
        const fill  = fills[level] || GREY_BG;
        const colW  = Math.floor(CONTENT_W / 3);
        return new TableRow({
            children: [
                new TableCell({
                    borders: allThin(GREY_RULE),
                    width: { size: colW, type: WidthType.DXA },
                    shading: { fill, type: ShadingType.CLEAR },
                    margins: { top: 80, bottom: 80, left: 120, right: 120 },
                    children: [para([boldRun(level + ' Risk', { size: 20 })])],
                }),
                new TableCell({
                    borders: allThin(GREY_RULE),
                    width: { size: colW, type: WidthType.DXA },
                    margins: { top: 80, bottom: 80, left: 120, right: 120 },
                    children: [para([run(String(count), { size: 20 })])],
                }),
                new TableCell({
                    borders: allThin(GREY_RULE),
                    width: { size: colW, type: WidthType.DXA },
                    margins: { top: 80, bottom: 80, left: 120, right: 120 },
                    children: [para([run(pct + '%', { size: 20 })])],
                }),
            ],
        });
    });

    const riskHeaderRow = new TableRow({
        children: ['Risk Level', 'Count', 'Percentage'].map(h => {
            const colW = Math.floor(CONTENT_W / 3);
            return new TableCell({
                borders: allThin(NAVY),
                width: { size: colW, type: WidthType.DXA },
                shading: { fill: NAVY, type: ShadingType.CLEAR },
                margins: { top: 80, bottom: 80, left: 120, right: 120 },
                children: [para([boldRun(h, { size: 20, color: WHITE })])],
            });
        }),
    });

    const riskTable = new Table({
        width: { size: CONTENT_W, type: WidthType.DXA },
        columnWidths: [Math.floor(CONTENT_W / 3), Math.floor(CONTENT_W / 3), Math.floor(CONTENT_W / 3)],
        rows: [riskHeaderRow, ...riskRows],
    });

    // ============================================
    // EXCEPTION DETAIL BLOCKS
    // ============================================
    const exceptionChildren = [];
    let exceptionIndex = 1;

    for (const [title, exceptions] of Object.entries(grouped)) {
        const primary   = exceptions[0];
        const multiInst = exceptions.length > 1;

        // Exception number + title banner
        exceptionChildren.push(
            new Paragraph({
                children: [
                    boldRun(`${exceptionIndex}. `, { size: 22, color: WHITE }),
                    boldRun(stripHtml(title), { size: 22, color: WHITE }),
                    ...(multiInst ? [run(`  (${exceptions.length} instances)`, { size: 20, color: 'D0E8F0' })] : []),
                ],
                shading: { fill: NAVY_LIGHT, type: ShadingType.CLEAR },
                spacing: { before: 280, after: 0 },
                indent: { left: 200, right: 200 },
                border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: GOLD } },
            })
        );

        // Exception Description
        const descText = stripHtml(generateExceptionDescription(primary, exceptions));
        exceptionChildren.push(
            blockLabel('Exception Description', TEAL),
            blockText(descText),
        );

        // Root Cause (optional)
        if (primary.rootCause) {
            exceptionChildren.push(
                blockLabel('Root Cause Analysis', GOLD),
                blockText(stripHtml(primary.rootCause)),
            );
        }

        // Analysis
        exceptionChildren.push(
            blockLabel('Detailed Analysis'),
            blockText(stripHtml(generateExceptionAnalysis(primary, exceptions))),
        );

        // Risk Analysis (optional)
        if (primary.riskAnalysis) {
            exceptionChildren.push(
                blockLabel('Risk Analysis & Assessment', GOLD),
                blockText(stripHtml(primary.riskAnalysis)),
            );
        }

        // Recommendation (optional)
        if (primary.recommendation) {
            exceptionChildren.push(
                blockLabel('Audit Recommendation', '155724'),
                blockText(stripHtml(generateRecommendationContent(exceptions))),
            );
        }

        // Management Response
        exceptionChildren.push(
            blockLabel('Management Response' + (multiInst ? 's' : ''), TEAL),
            blockText(stripHtml(generateManagementResponse(exceptions))),
            // divider between exceptions
            new Paragraph({
                children: [new TextRun('')],
                border: { bottom: { style: BorderStyle.SINGLE, size: 2, color: GREY_RULE } },
                spacing: { after: 160 },
            }),
        );

        exceptionIndex++;
    }

    // ============================================
    // APPLIED FILTERS TABLE
    // ============================================
    const filterEntries = Object.entries(getFilterValues())
        .filter(([, v]) => v)
        .map(([k, v]) => {
            const label = k.charAt(0).toUpperCase() + k.slice(1).replace(/([A-Z])/g, ' $1');
            const colW  = Math.floor(CONTENT_W / 2);
            return new TableRow({
                children: [
                    new TableCell({
                        borders: allThin(GREY_RULE),
                        width: { size: colW, type: WidthType.DXA },
                        shading: { fill: GREY_BG, type: ShadingType.CLEAR },
                        margins: { top: 60, bottom: 60, left: 120, right: 120 },
                        children: [para([boldRun(label, { size: 20 })])],
                    }),
                    new TableCell({
                        borders: allThin(GREY_RULE),
                        width: { size: colW, type: WidthType.DXA },
                        margins: { top: 60, bottom: 60, left: 120, right: 120 },
                        children: [para([run(String(v), { size: 20 })])],
                    }),
                ],
            });
        });

    const filterHeaderRow = new TableRow({
        children: ['Filter Parameter', 'Applied Value'].map(h => {
            const colW = Math.floor(CONTENT_W / 2);
            return new TableCell({
                borders: allThin(NAVY),
                width: { size: colW, type: WidthType.DXA },
                shading: { fill: NAVY, type: ShadingType.CLEAR },
                margins: { top: 80, bottom: 80, left: 120, right: 120 },
                children: [para([boldRun(h, { size: 20, color: WHITE })])],
            });
        }),
    });

    const filterTable = new Table({
        width: { size: CONTENT_W, type: WidthType.DXA },
        columnWidths: [Math.floor(CONTENT_W / 2), Math.floor(CONTENT_W / 2)],
        rows: filterEntries.length
            ? [filterHeaderRow, ...filterEntries]
            : [filterHeaderRow],
    });

    // ============================================
    // ASSEMBLE DOCUMENT
    // ============================================
    const bodyChildren = [
        // ---- Executive Summary ----
        sectionHeading('Executive Summary'),
        spacer(4),
        metricsTable,
        spacer(16),
        ...execSummary.split('\n').filter(s => s.trim()).map(line =>
            new Paragraph({
                children: [run(line, { size: 21, color: TEXT_DARK })],
                alignment: AlignmentType.JUSTIFIED,
                spacing: { after: 140 },
            })
        ),

        // ---- Risk Distribution ----
        spacer(16),
        sectionHeading('Risk Distribution'),
        spacer(8),
        ...(riskRows.length ? [riskTable] : [
            para([run('No risk rate data available for the selected filters.', { size: 20, color: TEXT_MID })])
        ]),

        // ---- Exception Details ----
        new Paragraph({ children: [new PageBreak()] }),
        sectionHeading('Exception Details'),
        spacer(8),
        ...exceptionChildren,

        // ---- Applied Filters ----
        new Paragraph({ children: [new PageBreak()] }),
        sectionHeading('Report Parameters & Applied Filters'),
        spacer(8),
        filterTable,
        spacer(20),

        // ---- Signature block ----
        sectionHeading('Prepared By'),
        spacer(60),
        new Paragraph({
            children: [run('_'.repeat(40), { color: TEXT_MID })],
            spacing: { after: 60 },
        }),
        para([boldRun('Internal Audit Team', { size: 20 })]),
        para([run('Best Point Savings and Loans Limited', { size: 20, color: TEXT_MID })]),
        para([run(`Date: ${new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' })}`, { size: 20, color: TEXT_MID })]),
    ];

    const doc = new Document({
        creator: 'Best Point Internal Audit',
        title: reportTitle,
        description: `Branch Exception Report — ${filters.branch}`,
        styles: {
            default: {
                document: { run: { font: 'Calibri', size: 22, color: TEXT_DARK } },
            },
        },
        numbering: {
            config: [
                {
                    reference: 'bullets',
                    levels: [{
                        level: 0,
                        format: LevelFormat.BULLET,
                        text: '•',
                        alignment: AlignmentType.LEFT,
                        style: { paragraph: { indent: { left: 720, hanging: 360 } } },
                    }],
                },
            ],
        },
        sections: [
            // ---- Cover page (no header/footer) ----
            {
                properties: {
                    page: {
                        size: { width: PAGE_W, height: PAGE_H },
                        margin: { top: MARGIN, right: MARGIN, bottom: MARGIN, left: MARGIN },
                    },
                },
                children: coverChildren,
            },
            // ---- Body pages ----
            {
                properties: {
                    page: {
                        size: { width: PAGE_W, height: PAGE_H },
                        margin: { top: 1000, right: MARGIN, bottom: 1000, left: MARGIN },
                    },
                },
                headers: {
                    default: new Header({ children: headerChildren }),
                },
                footers: {
                    default: new Footer({ children: [footerPara] }),
                },
                children: bodyChildren,
            },
        ],
    });

    return Packer.toBuffer(doc);
}

// ============================================
// UTILITY: strip HTML tags from a string
// ============================================
function stripHtml(html) {
    if (!html) return '';
    return html
        .replace(/<br\s*\/?>/gi, '\n')
        .replace(/<\/p>/gi, '\n')
        .replace(/<\/div>/gi, '\n')
        .replace(/<[^>]+>/g, '')
        .replace(/&amp;/g, '&')
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&nbsp;/g, ' ')
        .replace(/\n{3,}/g, '\n\n')
        .trim();
}
