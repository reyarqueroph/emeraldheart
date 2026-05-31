/* ============================================
   eHeart — Immersive PDF Viewer (PDF.js)
   ============================================ */

const PDFJS_CDN = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
const PDFJS_WORKER = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

let _pdfJsReady = false;
let _pdfJsLoading = false;
const _pdfJsQueue = [];

function loadPdfJs(cb) {
    if (_pdfJsReady) return cb();
    _pdfJsQueue.push(cb);
    if (_pdfJsLoading) return;
    _pdfJsLoading = true;
    const s = document.createElement('script');
    s.src = PDFJS_CDN;
    s.onload = () => {
        pdfjsLib.GlobalWorkerOptions.workerSrc = PDFJS_WORKER;
        _pdfJsReady = true;
        _pdfJsQueue.forEach(fn => fn());
        _pdfJsQueue.length = 0;
    };
    document.head.appendChild(s);
}

/**
 * Render a PDF into a container element.
 * @param {string} url       - Authenticated PDF URL
 * @param {string} containerId - ID of the .pdf-pages-area element
 * @param {object} state     - Shared viewer state object
 */
function renderPdfPages(url, containerId, state) {
    const area = document.getElementById(containerId);
    if (!area) return;

    area.innerHTML = `<div class="pdf-loading"><div class="pdf-spinner"></div><p>Loading document…</p></div>`;

    loadPdfJs(() => {
        pdfjsLib.getDocument({ url, withCredentials: true }).promise.then(pdf => {
            state.pdf = pdf;
            state.totalPages = pdf.numPages;
            updatePageCount(state);
            area.innerHTML = '';
            renderAllPages(area, state);
        }).catch(() => {
            area.innerHTML = `<div class="pdf-error"><i class="fas fa-exclamation-circle"></i><p>Unable to load document.<br>Please try downloading it instead.</p></div>`;
        });
    });
}

function renderAllPages(area, state) {
    const pdf = state.pdf;
    const scale = state.scale || 1.4;

    for (let i = 1; i <= pdf.numPages; i++) {
        const wrap = document.createElement('div');
        wrap.className = 'pdf-page-wrap';
        wrap.id = `${state.id}-page-${i}`;

        const canvas = document.createElement('canvas');
        wrap.appendChild(canvas);

        const numLabel = document.createElement('div');
        numLabel.className = 'pdf-page-num';
        numLabel.textContent = `${i} / ${pdf.numPages}`;
        wrap.appendChild(numLabel);

        area.appendChild(wrap);

        pdf.getPage(i).then(page => {
            const viewport = page.getViewport({ scale });
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            page.render({ canvasContext: canvas.getContext('2d'), viewport });
        });
    }
}

function updatePageCount(state) {
    const el = document.getElementById(`${state.id}-page-count`);
    if (el) el.textContent = state.totalPages || '—';
}

/**
 * Build and return the full PDF viewer HTML string.
 * @param {string} pdfUrl    - Authenticated PDF URL
 * @param {string} viewerId  - Unique ID prefix for this viewer instance
 * @param {string} filename  - Original filename for download
 */
function buildPdfViewer(pdfUrl, viewerId, filename) {
    return `
<div class="pdf-viewer-wrap" id="${viewerId}-wrap">
    <div class="pdf-toolbar">
        <div class="pdf-toolbar-left">
            <button class="pdf-tool-btn" title="Zoom out" onclick="pdfZoom('${viewerId}',-0.2)"><i class="fas fa-search-minus"></i></button>
            <span class="pdf-zoom-label" id="${viewerId}-zoom">140%</span>
            <button class="pdf-tool-btn" title="Zoom in" onclick="pdfZoom('${viewerId}',0.2)"><i class="fas fa-search-plus"></i></button>
        </div>
        <div class="pdf-toolbar-center">
            <i class="fas fa-file-pdf" style="color:var(--pru-red);font-size:12px;"></i>
            <span id="${viewerId}-page-count">—</span> pages
        </div>
        <div class="pdf-toolbar-right">
            <a class="pdf-tool-btn accent" href="${pdfUrl}" download="${filename}" title="Download PDF">
                <i class="fas fa-download"></i>
            </a>
        </div>
    </div>
    <div class="pdf-pages-area" id="${viewerId}-area"></div>
</div>`;
}

/* Viewer state registry */
const _viewers = {};

/**
 * Initialize a PDF viewer.
 * Call after injecting buildPdfViewer() HTML into the DOM.
 */
function initPdfViewer(viewerId, pdfUrl) {
    const state = { id: viewerId, scale: 1.4, pdf: null, totalPages: 0, url: pdfUrl };
    _viewers[viewerId] = state;
    renderPdfPages(pdfUrl, `${viewerId}-area`, state);
}

function pdfZoom(viewerId, delta) {
    const state = _viewers[viewerId];
    if (!state || !state.pdf) return;

    state.scale = Math.min(3.0, Math.max(0.6, state.scale + delta));

    const zoomEl = document.getElementById(`${viewerId}-zoom`);
    if (zoomEl) zoomEl.textContent = Math.round(state.scale * 100) + '%';

    const area = document.getElementById(`${viewerId}-area`);
    if (!area) return;
    area.innerHTML = '';
    renderAllPages(area, state);
}
