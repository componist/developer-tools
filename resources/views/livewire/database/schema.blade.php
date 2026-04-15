{{-- Schema-Daten für Alpine (muss vor x-data ausgewertet werden) --}}
<script>
  window.__schemaFromDb = @json($schemaForView ?? ['tables' => [], 'relations' => []]);
</script>
<style>
  .schema-editor .canvas-inner {
    background-image:
    linear-gradient(to right, rgba(30, 64, 175, 0.16) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(30, 64, 175, 0.16) 1px, transparent 1px);
    background-size: 20px 20px;
    background-position: 0 0;
  }
  .schema-editor [x-cloak] { display: none !important; }
  .schema-editor .canvas-inner { transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
  .schema-editor .relation-label--compact .relation-label-btn {
    min-width: 1.5rem;
    max-width: 1.5rem;
    padding: 0.25rem;
    font-size: 0.65rem;
  }
  .schema-editor .relation-label--compact .relation-label-btn span { display: none; }
  .schema-editor .relation-label--compact .relation-label-btn::after {
    content: '↔';
    font-size: 0.7rem;
  }
</style>
<div class="schema-editor flex flex-col overflow-hidden font-mono antialiased rounded-lg border border-slate-200 dark:border-slate-600"
     style="height: calc(100vh - 11rem);"
     x-data="databaseManager(window.__schemaFromDb || {})"
     x-init="init()"
     :class="isDark ? 'bg-slate-950 text-slate-200' : 'bg-slate-100 text-slate-900'">
  <div class="flex flex-col flex-1 min-h-0">
    <!-- Toolbar -->
    <header class="flex-none backdrop-blur px-4 py-3 flex items-center justify-between gap-4 border-b"
            :class="isDark ? 'border-slate-700/60 bg-slate-900/80' : 'border-slate-200 bg-white'">
      <div class="flex items-center gap-3">
        <h1 class="text-lg font-semibold text-teal-500 tracking-tight">Database Schema</h1>
        <span class="text-sm text-slate-400" x-text="'Tabellen: ' + tables.length + ' · Relationen: ' + relations.length"></span>
      </div>
      <div class="flex items-center gap-2">
        <button type="button"
                @click="autoArrangeTables()"
                class="px-3 py-1.5 rounded-lg bg-slate-600 hover:bg-slate-500 text-white text-sm font-medium transition"
                title="Tabellen überschneidungsfrei anordnen">
          Auto-Arrangieren
        </button>
        <div class="flex items-center gap-0.5 border-l border-slate-600 pl-2 ml-2">
          <button type="button"
                  @click="zoomOut()"
                  :disabled="zoomLevel <= 0.5"
                  class="p-1.5 rounded-lg bg-slate-600 hover:bg-slate-500 disabled:opacity-40 disabled:cursor-not-allowed text-white transition"
                  title="Verkleinern"
                  aria-label="Verkleinern">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
          </button>
          <span class="text-slate-400 text-xs min-w-[2.5rem] text-center" x-text="Math.round(zoomLevel * 100) + '%'"></span>
          <button type="button"
                  @click="zoomIn()"
                  :disabled="zoomLevel >= 2"
                  class="p-1.5 rounded-lg bg-slate-600 hover:bg-slate-500 disabled:opacity-40 disabled:cursor-not-allowed text-white transition"
                  title="Vergrößern"
                  aria-label="Vergrößern">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          </button>
        </div>
        <div class="flex items-center gap-2 border-l border-slate-600 pl-2 ml-2">
        <button type="button"
                @click="showValidationPanel = !showValidationPanel"
                :class="[
                  'px-3 py-1.5 rounded-lg text-sm font-medium transition',
                  issues.length ? 'bg-amber-600 hover:bg-amber-500 text-white' : 'bg-slate-600 hover:bg-slate-500 text-white'
                ]"
                :title="issues.length ? issues.length + ' Validierungswarnung(en)' : 'Validierung'">
          <span x-text="issues.length ? '⚠ ' + issues.length : '✓'"></span>
        </button>
        <button type="button"
                @click="showRelationPanel = !showRelationPanel"
                :class="[
                  'px-3 py-1.5 rounded-lg text-sm font-medium transition',
                  showRelationPanel ? 'bg-teal-600' : 'bg-slate-600 hover:bg-slate-500',
                  'text-white'
                ]"
                :title="'Relationen-Panel (' + relations.length + ')'">
          Relationen
        </button>
        </div>
      </div>
    </header>

    <!-- Canvas (einziger scrollbarer Bereich) -->
    <main class="flex-1 min-h-0 overflow-auto relative"
          :class="isDark ? 'bg-slate-900/40' : 'bg-slate-100/60'"
          id="canvas-wrap"
          role="region"
          aria-label="Schema-Canvas"
          @wheel="onCanvasWheel($event)"
          @mousedown.self="onCanvasMouseDown($event)"
          @touchstart.self.prevent="startCanvasPanTouch($event)">
      <div class="canvas-inner relative h-full w-full p-8 transition-transform duration-150 select-none"
           :style="canvasMinStyle + '; transform: scale(' + zoomLevel + '); transform-origin: 0 0;'"
           :class="isPanning ? 'cursor-grabbing' : 'cursor-grab'"
           @click.self="editingRelationId = null"
           @mousedown.self="startCanvasPan($event)"
           @touchstart.self.prevent="startCanvasPanTouch($event)"
           x-effect="drawRelationLines()">
        <!-- SVG für Relation-Linien: z-index 1, immer hinter Tabellen -->
        <svg class="absolute inset-0 w-full h-full overflow-visible"
             style="z-index: 1; pointer-events: none;"
             aria-hidden="true"
             id="relations-svg">
          <g id="relations-lines"></g>
          <g id="relation-preview" style="pointer-events: none;"></g>
        </svg>

        <!-- Relation-Labels: z-index 2, Tabellen z-index 5 liegen darüber -->
        <template x-for="rel in relations" :key="rel.id">
          <div class="absolute -translate-x-1/2 -translate-y-1/2 flex flex-col items-center relation-label"
               style="z-index: 2"
               :class="{ 'relation-label--compact': relations.length > 12 && hoveredRelationId !== rel.id && editingRelationId !== rel.id }"
               :style="{ left: ((rel.x1 + rel.x2) / 2) + 'px', top: ((rel.y1 + rel.y2) / 2) + 'px' }">
            <button type="button"
                    :data-rel-id="rel.id"
                    @mouseenter="hoveredRelationId = rel.id"
                    @mouseleave="hoveredRelationId = null"
                    @click.stop="editingRelationId = (editingRelationId === rel.id ? null : rel.id)"
                    class="relation-label-btn text-xs font-semibold font-mono px-2 py-1 rounded border-2 transition-all duration-150 min-w-[4rem] truncate max-w-[18rem] hover:opacity-90 focus:ring-2 focus:ring-offset-1 focus:outline-none"
                    :class="[
                      highlightedIssueTarget?.relationId === rel.id ? 'ring-2 ring-offset-1' : '',
                      hoveredRelationId === rel.id || editingRelationId === rel.id ? 'ring-2 ring-offset-1' : '',
                      selectedTableId && rel.fromTable !== selectedTableId && rel.toTable !== selectedTableId ? 'opacity-30' : ''
                    ]"
                    :style="{
                      borderColor: highlightedIssueTarget?.relationId === rel.id ? 'rgb(251 191 36)' : getRelationStroke(rel),
                      color: highlightedIssueTarget?.relationId === rel.id ? 'rgb(251 191 36)' : getRelationStroke(rel),
                      background: highlightedIssueTarget?.relationId === rel.id ? 'rgba(251, 191, 36, 0.15)' : (isDark ? 'rgb(15 23 42)' : 'rgb(248 250 252)'),
                      '--tw-ring-color': highlightedIssueTarget?.relationId === rel.id ? 'rgb(251 191 36)' : getRelationStroke(rel)
                    }"
                    title="Relation bearbeiten"
                    aria-label="Relation bearbeiten">
              <span x-text="(rel.fromColName || '?') + ' → ' + (rel.toColName || '?')"></span>
            </button>
          </div>
        </template>

        <!-- Table cards - z-index 5 über Relation-Labels, damit Tabellen immer bearbeitbar bleiben -->
        <template x-for="table in tables" :key="table.id">
          <div class="absolute"
               style="z-index: 5;"
               :style="{ left: table.x + 'px', top: table.y + 'px' }"
               :id="'table-' + table.id">
            <div class="overflow-hidden select-none rounded-xl shadow-xl border transition-shadow duration-300"
                 @click.stop="selectedTableId = (selectedTableId === table.id ? null : table.id)"
                 :class="[
                   isDark ? 'bg-slate-800 border-slate-600 shadow-black/20' : 'bg-white border-slate-200 shadow-slate-300/60',
                   selectedTableId === table.id ? 'ring-2 ring-teal-500' : '',
                   highlightedIssueTarget?.tableId === table.id ? 'ring-2 ring-amber-400 shadow-amber-400/30' : ''
                 ]">
            <!-- Table header -->
            <div class="px-3 py-2 border-b flex items-center justify-between gap-2"
                 :class="isDark ? 'bg-slate-700/80 border-slate-600' : 'bg-slate-100 border-slate-200'">
              <span @mousedown="startDrag($event, table)"
                    @touchstart.prevent="startDragTouch($event, table)"
                    class="shrink-0 p-0.5 rounded text-slate-400 hover:text-slate-300 touch-none cursor-grab active:cursor-grabbing"
                    title="Tabelle verschieben">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <circle cx="9" cy="6" r="1.25"/><circle cx="9" cy="12" r="1.25"/><circle cx="9" cy="18" r="1.25"/>
                  <circle cx="15" cy="6" r="1.25"/><circle cx="15" cy="12" r="1.25"/><circle cx="15" cy="18" r="1.25"/>
                </svg>
              </span>
              <input type="text"
                     x-model="table.name"
                     @click.stop
                     class="flex-1 bg-transparent border-none font-semibold text-sm outline-none focus:ring-0 placeholder-slate-500"
                     :class="isDark ? 'text-teal-400' : 'text-teal-700'"
                     placeholder="Tabellenname" />
            </div>
            <!-- Columns -->
            <div class="py-1">
              <template x-for="(col, colIdx) in table.columns" :key="col.id">
                <div class="px-3 py-1.5 flex items-center gap-2 group border-b last:border-0 transition-colors duration-200"
                   :class="[
                     isDark ? 'hover:bg-slate-700/50 border-slate-700/50' : 'hover:bg-slate-100 border-slate-200',
                     highlightedIssueTarget?.tableId === table.id && highlightedIssueTarget?.columnId === col.id && 'ring-1 ring-amber-400 bg-amber-500/20'
                   ]"
                   :data-table-id="table.id"
                   :data-column-id="col.id">
                  <input type="text"
                         x-model="col.name"
                         @click.stop
                         class="flex-1 bg-transparent border-none text-sm outline-none py-0.5"
                         :class="isDark ? 'text-slate-300' : 'text-slate-800'"
                         placeholder="Spalte" />
                  <span class="w-5 h-5 shrink-0 flex items-center justify-center rounded font-bold"
                        :class="col.nullable ? 'text-red-500 bg-red-500/20' : 'text-slate-400'"
                        :title="col.nullable ? 'Nullable (NULL erlaubt)' : 'Nicht nullable (NOT NULL)'">N</span>
                </div>
              </template>
            </div>
          </div>
        </div>
        </template>
      </div>
    </main>

    <!-- Relationen-Panel: Liste aller Relationen für Bearbeitung bei vielen Relationen -->
    <div x-cloak x-show="showRelationPanel"
         @keydown.escape.window="showRelationPanel && (showRelationPanel = false)"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-x-4"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         class="fixed top-14 right-4 bottom-20 z-40 w-80 flex flex-col rounded-xl shadow-2xl border overflow-hidden"
         :class="isDark ? 'bg-slate-900 border-slate-600' : 'bg-white border-slate-200'">
      <div class="px-4 py-3 border-b flex items-center justify-between shrink-0"
           :class="isDark ? 'border-slate-700 bg-slate-800/80' : 'border-slate-200 bg-slate-50'">
        <h3 class="text-sm font-semibold" :class="isDark ? 'text-slate-200' : 'text-slate-800'">Relationen</h3>
        <button type="button" @click="showRelationPanel = false" class="p-1 rounded text-slate-400 hover:text-slate-300">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
      </div>
      <div class="flex-1 overflow-y-auto p-2 min-h-0">
        <p x-show="relations.length === 0" class="text-sm py-4 text-center" :class="isDark ? 'text-slate-400' : 'text-slate-500'">Keine Relationen.</p>
        <template x-for="rel in relations" :key="rel.id">
          <div class="mb-2 rounded-lg border transition-colors"
               :class="[
                 isDark ? 'border-slate-600 hover:bg-slate-800/80' : 'border-slate-200 hover:bg-slate-50',
                 editingRelationId === rel.id ? 'ring-2 ring-teal-500' : ''
               ]">
            <button type="button"
                    @click="editingRelationId = (editingRelationId === rel.id ? null : rel.id); focusRelation(rel.id)"
                    class="w-full text-left px-3 py-2 text-sm flex flex-col gap-0.5">
              <span class="font-medium" :class="isDark ? 'text-slate-200' : 'text-slate-800'">
                <span x-text="getTableName(rel.fromTable)"></span>
                <span class="text-slate-400"> → </span>
                <span x-text="getTableName(rel.toTable)"></span>
              </span>
              <span class="text-xs" :class="isDark ? 'text-slate-400' : 'text-slate-500'"
                    x-text="(rel.fromColName || '?') + ' → ' + (rel.toColName || '?')"></span>
            </button>
          </div>
        </template>
      </div>
    </div>

    <!-- Validierungs-Panel -->
    <div x-cloak x-show="showValidationPanel"
         @keydown.escape.window="showValidationPanel && (showValidationPanel = false)"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="fixed bottom-20 right-4 z-40 w-96 max-h-64 overflow-hidden rounded-xl shadow-2xl border"
         :class="isDark ? 'bg-slate-900 border-slate-600' : 'bg-white border-slate-200'">
      <div class="px-4 py-3 border-b flex items-center justify-between"
           :class="isDark ? 'border-slate-700 bg-slate-800/80' : 'border-slate-200 bg-slate-50'">
        <h3 class="text-sm font-semibold" :class="isDark ? 'text-slate-200' : 'text-slate-800'">Validierung</h3>
        <button type="button" @click="showValidationPanel = false" class="p-1 rounded text-slate-400 hover:text-slate-300">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
      </div>
      <div class="overflow-y-auto max-h-48 p-2">
        <p x-show="issues.length === 0" class="text-sm py-4 text-center" :class="isDark ? 'text-slate-400' : 'text-slate-500'">Keine Validierungswarnungen.</p>
        <template x-for="(issue, idx) in issues" :key="idx">
          <button type="button"
                  @click="focusIssue(issue); showValidationPanel = false"
                  class="w-full text-left px-3 py-2 rounded-lg text-sm hover:bg-amber-500/20 transition flex items-start gap-2 group"
                  :class="isDark ? 'text-slate-300 hover:bg-amber-500/20' : 'text-slate-700 hover:bg-amber-100'">
            <span class="shrink-0 text-amber-500 mt-0.5">⚠</span>
            <span class="flex-1" x-text="issue.message"></span>
            <span class="text-xs text-slate-400 group-hover:text-teal-500">→</span>
          </button>
        </template>
      </div>
    </div>

    <!-- Toast-Container -->
    <div class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 max-w-sm" aria-live="polite">
      <template x-for="t in toasts" :key="t.id">
        <div x-show="!t.removing"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="px-4 py-3 rounded-lg shadow-lg border flex items-center gap-3 font-medium text-sm"
             :class="{
               'bg-red-900/95 border-red-700 text-red-100': t.type === 'error',
               'bg-amber-900/95 border-amber-700 text-amber-100': t.type === 'warning',
               'bg-teal-900/95 border-teal-700 text-teal-100': t.type === 'success',
               'bg-slate-800/95 border-slate-600 text-slate-200': t.type === 'info' || !t.type
             }">
          <span class="shrink-0 w-5 text-center" x-text="t.type === 'error' ? '✕' : t.type === 'warning' ? '⚠' : t.type === 'success' ? '✓' : 'ℹ'"></span>
          <span x-text="t.message" class="flex-1"></span>
        </div>
      </template>
    </div>
  </div>

  <script>
    function databaseManager(initial) {
      const tables = (initial && Array.isArray(initial.tables)) ? initial.tables : [];
      const relations = (initial && Array.isArray(initial.relations)) ? initial.relations : [];
      const nextTableId = tables.length ? Math.max(1, ...tables.map(t => parseInt(String(t.id).replace(/\D/g, ''), 10) || 0)) + 1 : 1;
      const nextColId = tables.length ? Math.max(1, ...tables.flatMap(t => (t.columns || []).map(c => parseInt(String(c.id).replace(/\D/g, ''), 10) || 0))) + 1 : 1;
      const nextRelId = relations.length ? Math.max(1, ...relations.map(r => parseInt(String(r.id).replace(/\D/g, ''), 10) || 0)) + 1 : 1;
      return {
        isDark: true,
        tables: tables,
        relations: relations,
        tableSpacingX: 720,
        tableSpacingY: 600,
        tableElements: {},
        selectedTableId: null,
        gridSize: 20,
        editingRelationId: null,
        relationTypes: ['1:1', '1:N', 'N:1', 'M:N', '1:0..1', '0..1:1', '1:0..*', '0..*:1'],
        relationTypeLabels: {
          '1:1': 'One-to-One',
          '1:N': 'One-to-Many',
          'N:1': 'Many-to-One',
          'M:N': 'Many-to-Many',
          '1:0..1': 'One-to-Zero-or-One',
          '0..1:1': 'Zero-or-One-to-One',
          '1:0..*': 'One-to-Zero-or-Many',
          '0..*:1': 'Zero-or-Many-to-One'
        },
        getRelationTypeLabel(type) {
          return this.relationTypeLabels[type || '1:N'] || type || '1:N';
        },
        getTableName(tableId) {
          const t = this.tables.find(tbl => tbl.id === tableId);
          return t?.name || tableId;
        },
        nextTableId: nextTableId,
        nextColId: nextColId,
        nextRelId: nextRelId,
        dragState: null,
        showValidationPanel: false,
        showRelationPanel: false,
        zoomLevel: 1,
        isPanning: false,
        drawRelationLinesRaf: null,
        toasts: [],
        highlightedIssueTarget: null,
        hoveredRelationId: null,

        estimateTableSize(table) {
          const colCount = (table.columns && table.columns.length) || 0;
          const lengths = (table.columns && table.columns.length) ? table.columns.map(c => (c.name && c.name.length) || 8) : [];
          const maxColName = lengths.length ? Math.max(12, ...lengths) : 12;
          const baseW = 200 + maxColName * 10 + colCount * 115;
          const baseH = 56 + colCount * 34;
          const padding = 40;
          return { w: Math.min(baseW + padding, 560), h: baseH + padding };
        },

        get canvasMinStyle() {
          if (this.tables.length === 0) return 'min-width: 1200px; min-height: 600px;';
          let maxX = 0, maxY = 0;
          this.tables.forEach(t => {
            const s = this.estimateTableSize(t);
            maxX = Math.max(maxX, t.x + s.w);
            maxY = Math.max(maxY, t.y + s.h);
          });
          const w = Math.max(1200, maxX + 120);
          const h = Math.max(600, maxY + 120);
          return `min-width: ${w}px; min-height: ${h}px;`;
        },

        get issues() {
          const issues = [];
          const nameMap = {};
          this.tables.forEach(t => {
            if (!t.name) return;
            const key = t.name.toLowerCase();
            nameMap[key] = nameMap[key] || [];
            nameMap[key].push(t);
          });
          Object.entries(nameMap).forEach(([key, arr]) => {
            if (arr.length > 1) {
              const t = arr[0];
              issues.push({ message: 'Tabellenname "' + (t.name || key) + '" ist mehrfach vorhanden.', type: 'duplicate_table', target: { tableId: t.id } });
            }
          });
          const relByCol = {};
          this.relations.forEach(r => {
            if (r.fromColumn) {
              const k = r.fromTable + '|' + r.fromColumn;
              relByCol[k] = true;
            }
            if (r.toColumn) {
              const k2 = r.toTable + '|' + r.toColumn;
              relByCol[k2] = true;
            }
          });
          this.tables.forEach(t => {
            t.columns.forEach(c => {
              if (c.type === 'FK') {
                const k = t.id + '|' + c.id;
                if (!relByCol[k]) {
                  issues.push({ message: 'FK-Spalte "' + (c.name || '?') + '" in Tabelle "' + (t.name || '?') + '" hat keine Relation.', type: 'orphan_fk', target: { tableId: t.id, columnId: c.id } });
                }
              }
            });
          });
          this.relations.forEach(r => {
            if (!r.fromColumn || !r.toColumn) {
              const fromT = this.tables.find(t => t.id === r.fromTable);
              const toT = this.tables.find(t => t.id === r.toTable);
              const fromName = fromT?.name || '?';
              const toName = toT?.name || '?';
              const label = (r.fromColName || r.toColName) ? (r.fromColName || r.toColName) : r.id;
              issues.push({ message: 'Relation "' + label + '" zwischen "' + fromName + '" und "' + toName + '" hat keine Spaltenreferenz.', type: 'relation_no_columns', target: { relationId: r.id } });
            }
          });
          return issues;
        },

        toast(message, type = 'info') {
          const id = 'toast-' + Date.now() + '-' + Math.random().toString(36).slice(2);
          this.toasts.push({ id, message, type, removing: false });
          setTimeout(() => {
            const t = this.toasts.find(to => to.id === id);
            if (t) {
              t.removing = true;
              setTimeout(() => {
                const idx = this.toasts.findIndex(to => to.id === id);
                if (idx >= 0) this.toasts.splice(idx, 1);
              }, 200);
            }
          }, 4500);
        },

        focusIssue(issue) {
          if (!issue?.target) return;
          const { tableId, columnId, relationId } = issue.target;
          this.highlightedIssueTarget = issue.target;
          setTimeout(() => { this.highlightedIssueTarget = null; }, 2500);
          if (tableId) {
            const el = document.getElementById('table-' + tableId);
            if (el) {
              el.scrollIntoView({ behavior: 'smooth', block: 'center' });
              const wrap = document.getElementById('canvas-wrap');
              if (wrap) wrap.scrollLeft = Math.max(0, el.offsetLeft - wrap.clientWidth / 2 + (el.offsetWidth || 200) / 2);
              if (columnId) {
                const row = document.querySelector(`[data-table-id="${tableId}"][data-column-id="${columnId}"]`);
                if (row) row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
              }
            }
          } else           if (relationId) {
            const relBtn = document.querySelector('[data-rel-id="' + relationId + '"]');
            if (relBtn) relBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
        },

        focusRelation(relId) {
          const rel = this.relations.find(r => r.id === relId);
          if (!rel) return;
          this.hoveredRelationId = relId;
          const relBtn = document.querySelector('[data-rel-id="' + relId + '"]');
          if (relBtn) relBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
          const wrap = document.getElementById('canvas-wrap');
          if (wrap && relBtn) {
            const rect = relBtn.getBoundingClientRect();
            const wrapRect = wrap.getBoundingClientRect();
            const scrollX = wrap.scrollLeft + (rect.left - wrapRect.left) - wrap.clientWidth / 2 + rect.width / 2;
            const scrollY = wrap.scrollTop + (rect.top - wrapRect.top) - wrap.clientHeight / 2 + rect.height / 2;
            wrap.scrollTo({ left: Math.max(0, scrollX), top: Math.max(0, scrollY), behavior: 'smooth' });
          }
        },

        init() {
          if (this.tables.length > 0) {
            this.autoArrangeTables(true);
          }
          const runPositions = () => {
            this.refreshTableElements();
            this.updateRelationPositions();
          };
          setTimeout(runPositions, 50);
          setTimeout(runPositions, 200);
          setTimeout(runPositions, 450);
        },

        updateTableEl(tableId) {
          const el = document.getElementById('table-' + tableId);
          if (el) this.tableElements[tableId] = el;
        },

        clientToCanvas(clientX, clientY) {
          const wrap = document.getElementById('canvas-wrap');
          if (!wrap) return null;
          const rect = wrap.getBoundingClientRect();
          const zoom = this.zoomLevel;
          return {
            x: (clientX - rect.left + wrap.scrollLeft) / zoom,
            y: (clientY - rect.top + wrap.scrollTop) / zoom
          };
        },

        startDrag(e, table) {
          const pt = this.clientToCanvas(e.clientX, e.clientY);
          if (!pt) return;
          const offsetX = pt.x - table.x;
          const offsetY = pt.y - table.y;
          const snap = (v) => Math.round(v / this.gridSize) * this.gridSize;
          const onMove = (ev) => {
            if (!this.dragState) return;
            const p = this.clientToCanvas(ev.clientX, ev.clientY);
            if (!p) return;
            table.x = snap(p.x - offsetX);
            table.y = snap(p.y - offsetY);
            this.updateRelationPositions();
          };
          this.dragState = { table, offsetX, offsetY };
          const onUp = () => {
            this.dragState = null;
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
          };
          document.addEventListener('mousemove', onMove);
          document.addEventListener('mouseup', onUp);
        },

        startDragTouch(e, table) {
          const touch = e.touches[0];
          if (!touch) return;
          const pt = this.clientToCanvas(touch.clientX, touch.clientY);
          if (!pt) return;
          const offsetX = pt.x - table.x;
          const offsetY = pt.y - table.y;
          const snap = (v) => Math.round(v / this.gridSize) * this.gridSize;
          const onMove = (ev) => {
            const t = ev.touches[0];
            if (!t) return;
            const p = this.clientToCanvas(t.clientX, t.clientY);
            if (!p) return;
            table.x = snap(p.x - offsetX);
            table.y = snap(p.y - offsetY);
            this.updateRelationPositions();
          };
          const onEnd = () => {
            document.removeEventListener('touchmove', onMove);
            document.removeEventListener('touchend', onEnd);
            document.removeEventListener('touchcancel', onEnd);
          };
          document.addEventListener('touchmove', onMove, { passive: false });
          document.addEventListener('touchend', onEnd);
          document.addEventListener('touchcancel', onEnd);
        },

        refreshTableElements() {
          this.tables.forEach(t => this.updateTableEl(t.id));
        },

        getColumnPosition(tableId, columnId, side) {
          const el = document.querySelector(`[data-table-id="${tableId}"][data-column-id="${columnId}"]`);
          const wrap = document.getElementById('canvas-wrap');
          if (!el || !wrap) return null;
          const rect = el.getBoundingClientRect();
          const wrapRect = wrap.getBoundingClientRect();
          const zoom = this.zoomLevel;
          let y = rect.top - wrapRect.top + wrap.scrollTop + rect.height / 2;
          let x = side === 'right' ? rect.right - wrapRect.left + wrap.scrollLeft
            : side === 'left' ? rect.left - wrapRect.left + wrap.scrollLeft
            : rect.left - wrapRect.left + wrap.scrollLeft + rect.width / 2;
          x = x / zoom;
          y = y / zoom;
          return { x, y };
        },

        onCanvasMouseDown(e) {
          if (e.button !== 1 && !(e.button === 0 && e.altKey)) return;
          this.startCanvasPan(e);
        },

        startCanvasPan(e) {
          if (e.button !== 0 && e.button !== 1) return;
          e.preventDefault();
          const wrap = document.getElementById('canvas-wrap');
          if (!wrap) return;
          this.isPanning = true;
          const startX = e.clientX;
          const startY = e.clientY;
          const startLeft = wrap.scrollLeft;
          const startTop = wrap.scrollTop;
          const onMove = (ev) => {
            ev.preventDefault();
            const dx = ev.clientX - startX;
            const dy = ev.clientY - startY;
            wrap.scrollLeft = startLeft - dx;
            wrap.scrollTop = startTop - dy;
          };
          const onUp = () => {
            this.isPanning = false;
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
          };
          document.addEventListener('mousemove', onMove);
          document.addEventListener('mouseup', onUp);
        },

        startCanvasPanTouch(e) {
          const wrap = document.getElementById('canvas-wrap');
          if (!wrap) return;
          if (e.touches.length !== 1) return;
          this.isPanning = true;
          const touch = e.touches[0];
          const startX = touch.clientX;
          const startY = touch.clientY;
          const startLeft = wrap.scrollLeft;
          const startTop = wrap.scrollTop;
          const onMove = (ev) => {
            const t = ev.touches[0];
            if (!t) return;
            const dx = t.clientX - startX;
            const dy = t.clientY - startY;
            wrap.scrollLeft = startLeft - dx;
            wrap.scrollTop = startTop - dy;
          };
          const onEnd = () => {
            this.isPanning = false;
            document.removeEventListener('touchmove', onMove);
            document.removeEventListener('touchend', onEnd);
            document.removeEventListener('touchcancel', onEnd);
          };
          document.addEventListener('touchmove', onMove, { passive: false });
          document.addEventListener('touchend', onEnd);
          document.addEventListener('touchcancel', onEnd);
        },

        onCanvasTouchStart(e) {
          const wrap = document.getElementById('canvas-wrap');
          if (!wrap) return;
          if (e.touches.length !== 1) return;
          const touch = e.touches[0];
          const startX = touch.clientX;
          const startY = touch.clientY;
          const startLeft = wrap.scrollLeft;
          const startTop = wrap.scrollTop;
          const onMove = (ev) => {
            const t = ev.touches[0];
            if (!t) return;
            const dx = t.clientX - startX;
            const dy = t.clientY - startY;
            wrap.scrollLeft = startLeft - dx;
            wrap.scrollTop = startTop - dy;
          };
          const onEnd = () => {
            document.removeEventListener('touchmove', onMove);
            document.removeEventListener('touchend', onEnd);
            document.removeEventListener('touchcancel', onEnd);
          };
          document.addEventListener('touchmove', onMove, { passive: false });
          document.addEventListener('touchend', onEnd);
          document.addEventListener('touchcancel', onEnd);
        },

        zoomIn() {
          if (this.zoomLevel < 2) this.zoomLevel = Math.min(2, Math.round((this.zoomLevel + 0.1) * 10) / 10);
        },

        zoomOut() {
          if (this.zoomLevel > 0.5) this.zoomLevel = Math.max(0.5, Math.round((this.zoomLevel - 0.1) * 10) / 10);
        },

        onCanvasWheel(e) {
          if (!e.ctrlKey) return;
          e.preventDefault();
          if (e.deltaY < 0) this.zoomIn();
          else if (e.deltaY > 0) this.zoomOut();
        },

        addRelation(fromTableId, fromColumnId, toTableId, toColumnId, fromColName, toColName, relationType = '1:N') {
          this.relations.push({
            id: 'r' + this.nextRelId++,
            fromTable: fromTableId,
            fromColumn: fromColumnId,
            toTable: toTableId,
            toColumn: toColumnId,
            fromColName: fromColName || '',
            toColName: toColName || '',
            relationType: relationType || '1:N',
            x1: 0, y1: 0, x2: 0, y2: 0
          });
          this.updateRelationPositions();
        },

        updateRelationPositions() {
          const wrap = document.getElementById('canvas-wrap');
          this.relations.forEach(rel => {
            if (rel.fromColumn && rel.toColumn) {
              const fromCenter = this.getColumnPosition(rel.fromTable, rel.fromColumn, 'center');
              const toCenter = this.getColumnPosition(rel.toTable, rel.toColumn, 'center');
              if (fromCenter && toCenter) {
                const fromSide = fromCenter.x < toCenter.x ? 'right' : 'left';
                const toSide = fromCenter.x < toCenter.x ? 'left' : 'right';
                const fromPos = this.getColumnPosition(rel.fromTable, rel.fromColumn, fromSide);
                const toPos = this.getColumnPosition(rel.toTable, rel.toColumn, toSide);
                if (fromPos) { rel.x1 = fromPos.x; rel.y1 = fromPos.y; }
                if (toPos) { rel.x2 = toPos.x; rel.y2 = toPos.y; }
              }
            } else if (wrap) {
              const fromEl = this.tableElements[rel.fromTable];
              const toEl = this.tableElements[rel.toTable];
              if (fromEl && toEl) {
                const wrapRect = wrap.getBoundingClientRect();
                const r1 = fromEl.getBoundingClientRect();
                const r2 = toEl.getBoundingClientRect();
                const zoom = this.zoomLevel;
                rel.x1 = (r1.right - wrapRect.left + wrap.scrollLeft - 2) / zoom;
                rel.y1 = (r1.top - wrapRect.top + wrap.scrollTop + r1.height / 2) / zoom;
                rel.x2 = (r2.left - wrapRect.left + wrap.scrollLeft + 2) / zoom;
                rel.y2 = (r2.top - wrapRect.top + wrap.scrollTop + r2.height / 2) / zoom;
              }
            }
          });
          this.drawRelationLines();
        },

        relationColors: [
          '#2dd4bf', '#22d3ee', '#38bdf8', '#818cf8', '#a78bfa', '#c084fc', '#f472b6', '#fb923c',
          '#34d399', '#4ade80', '#a3e635', '#facc15', '#fbbf24', '#94a3b8', '#64748b'
        ],

        relationColorsLight: [
          '#0d9488', '#0891b2', '#0284c7', '#4f46e5', '#7c3aed', '#9333ea', '#db2777', '#ea580c',
          '#059669', '#16a34a', '#65a30d', '#ca8a04', '#d97706', '#475569', '#334155'
        ],

        getRelationColorIndex(fromTableId) {
          const ids = [...new Set(this.relations.map(r => r.fromTable))].sort();
          const idx = ids.indexOf(fromTableId);
          return idx >= 0 ? idx % this.relationColors.length : 0;
        },

        getRelationStroke(rel) {
          const colors = this.isDark ? this.relationColors : this.relationColorsLight;
          const idx = this.getRelationColorIndex(rel.fromTable);
          return colors[idx];
        },

        drawRelationLines() {
          if (this.drawRelationLinesRaf) cancelAnimationFrame(this.drawRelationLinesRaf);
          this.drawRelationLinesRaf = requestAnimationFrame(() => {
            this.drawRelationLinesRaf = null;
            this._drawRelationLinesImpl();
          });
        },

        buildRelationPath(rel) {
          const STEP = 56;
          const x1 = rel.x1, y1 = rel.y1, x2 = rel.x2, y2 = rel.y2;
          const goingRight = x2 > x1;

          // Pro Tabellen-Paar: jede Relation in eigener Spur (parallel, nicht untereinander)
          const pairCounts = {};
          this.relations.forEach(r => {
            const k = r.fromTable + '|' + r.toTable;
            pairCounts[k] = (pairCounts[k] || 0) + 1;
          });
          const key = rel.fromTable + '|' + rel.toTable;
          const count = pairCounts[key] || 1;
          const idx = this.relations.slice(0, this.relations.indexOf(rel) + 1).filter(r =>
            r.fromTable === rel.fromTable && r.toTable === rel.toTable
          ).length - 1;
          const laneSpacing = 28;
          const midY = (y1 + y2) / 2 + (idx - (count - 1) / 2) * laneSpacing;
          const xStagger = (idx - (count - 1) / 2) * 22;
          const dir = goingRight ? 1 : -1;
          const x1b = (goingRight ? x1 + STEP : x1 - STEP) + dir * xStagger;
          const x2b = (goingRight ? x2 - STEP : x2 + STEP) - dir * xStagger;

          return `M ${x1} ${y1} L ${x1b} ${y1} L ${x1b} ${midY} L ${x2b} ${midY} L ${x2b} ${y2} L ${x2} ${y2}`;
        },

        buildRelationEndMarker(_rel) {
          return null; // Nur einfacher Linienstrich, keine End-Marker (Kreis/Crowfoot)
        },

        _drawRelationLinesImpl() {
          const svgNS = 'http://www.w3.org/2000/svg';
          const linesEl = document.getElementById('relations-lines');
          const previewEl = document.getElementById('relation-preview');
          if (!linesEl || !previewEl) return;
          const self = this;
          linesEl.innerHTML = '';
          const hasHover = !!self.hoveredRelationId;
          const hasTableSelected = !!self.selectedTableId;
          const relBelongsToSelected = (r) => r.fromTable === self.selectedTableId || r.toTable === self.selectedTableId;
          const activeRelId = self.editingRelationId || self.hoveredRelationId;
          const sortedRels = [...this.relations].sort((a, b) => (a.id === activeRelId ? 1 : 0) - (b.id === activeRelId ? 1 : 0));
          sortedRels.forEach(rel => {
            const isHovered = self.hoveredRelationId === rel.id;
            const belongsToSelected = !hasTableSelected || relBelongsToSelected(rel);
            const stroke = self.getRelationStroke(rel);
            const d = self.buildRelationPath(rel);
            const path = document.createElementNS(svgNS, 'path');
            path.setAttribute('d', d);
            path.setAttribute('fill', 'none');
            path.setAttribute('stroke', stroke);
            path.setAttribute('stroke-width', isHovered ? '5' : '2.5');
            path.setAttribute('stroke-linecap', 'round');
            path.setAttribute('stroke-linejoin', 'round');
            path.setAttribute('data-rel-id', rel.id);
            path.style.pointerEvents = 'stroke';
            path.style.cursor = 'pointer';
            path.style.transition = 'stroke-width 0.15s ease, opacity 0.15s ease';
            let opacity = 1;
            if (hasHover && !isHovered) opacity = 0.35;
            else if (hasTableSelected && !belongsToSelected) opacity = 0.25;
            path.style.opacity = opacity;
            path.addEventListener('mouseenter', () => { self.hoveredRelationId = rel.id; self.drawRelationLines(); });
            path.addEventListener('mouseleave', () => { self.hoveredRelationId = null; self.drawRelationLines(); });
            linesEl.appendChild(path);
            const marker = self.buildRelationEndMarker(rel);
            if (marker) {
              if (marker.type === 'circle') {
                const circle = document.createElementNS(svgNS, 'circle');
                circle.setAttribute('cx', marker.cx);
                circle.setAttribute('cy', marker.cy);
                circle.setAttribute('r', marker.r);
                circle.setAttribute('fill', stroke);
                circle.setAttribute('stroke', stroke);
                circle.setAttribute('stroke-width', isHovered ? '2' : '1');
                circle.style.opacity = opacity;
                circle.style.pointerEvents = 'none';
                linesEl.appendChild(circle);
              } else if (marker.type === 'crowfoot') {
                marker.paths.forEach(p => {
                  const mp = document.createElementNS(svgNS, 'path');
                  mp.setAttribute('d', p);
                  mp.setAttribute('fill', 'none');
                  mp.setAttribute('stroke', stroke);
                  mp.setAttribute('stroke-width', isHovered ? '3' : '2');
                  mp.setAttribute('stroke-linecap', 'round');
                  mp.style.opacity = opacity;
                  mp.style.pointerEvents = 'none';
                  linesEl.appendChild(mp);
                });
              }
            }
          });
          previewEl.innerHTML = '';
        },

        autoArrangeTables(silent = false) {
          if (this.tables.length === 0) {
            if (!silent) this.toast('Keine Tabellen zum Arrangieren.', 'warning');
            return;
          }
          const snap = (v) => Math.round(v / this.gridSize) * this.gridSize;
          const margin = 120;
          const maxRowWidth = 2800;
          const ordered = this.computeLayoutOrder();

          let x = 60, y = 60, rowMaxHeight = 0;
          ordered.forEach((table) => {
            const s = this.estimateTableSize(table);
            if (x + s.w + margin > maxRowWidth && x > 60) {
              x = 60;
              y = snap(y + rowMaxHeight + margin);
              rowMaxHeight = 0;
            }
            table.x = snap(x);
            table.y = snap(y);
            x = x + s.w + margin;
            rowMaxHeight = Math.max(rowMaxHeight, s.h);
          });

          this.resolveOverlaps(25);
          if (!silent) {
            this.updateRelationPositions();
            this.toast(this.tables.length + ' Tabellen arrangiert.', 'success');
          }
        },

        resolveOverlaps(maxIterations = 15) {
          const snap = (v) => Math.round(v / this.gridSize) * this.gridSize;
          const margin = 48;
          let iter = 0;
          while (iter < maxIterations) {
            let moved = false;
            for (let i = 0; i < this.tables.length; i++) {
              const a = this.tables[i];
              const sa = this.estimateTableSize(a);
              const aRight = a.x + sa.w, aBottom = a.y + sa.h;
              for (let j = i + 1; j < this.tables.length; j++) {
                const b = this.tables[j];
                const sb = this.estimateTableSize(b);
                const bRight = b.x + sb.w, bBottom = b.y + sb.h;
                const overlapX = Math.min(aRight, bRight) - Math.max(a.x, b.x);
                const overlapY = Math.min(aBottom, bBottom) - Math.max(a.y, b.y);
                if (overlapX > -margin && overlapY > -margin) {
                  const shiftX = Math.max(overlapX + margin, margin);
                  const shiftY = Math.max(overlapY + margin, margin);
                  if (overlapX >= overlapY) {
                    b.x = snap(b.x + shiftX);
                    moved = true;
                  } else {
                    b.y = snap(b.y + shiftY);
                    moved = true;
                  }
                }
              }
            }
            if (!moved) break;
            iter++;
          }
        },

        computeLayoutOrder() {
          if (this.relations.length === 0) return [...this.tables];
          const outDegree = {};
          this.tables.forEach(t => { outDegree[t.id] = 0; });
          this.relations.forEach(r => {
            outDegree[r.fromTable] = (outDegree[r.fromTable] || 0) + 1;
          });
          const levels = {};
          const visited = new Set();
          let queue = this.tables.filter(t => outDegree[t.id] === 0).map(t => t.id);
          if (queue.length === 0) queue = [this.tables[0].id];
          let level = 0;
          while (queue.length > 0) {
            queue.forEach(id => { levels[id] = level; visited.add(id); });
            const next = [];
            this.relations.forEach(r => {
              if (queue.includes(r.toTable) && !visited.has(r.fromTable)) {
                next.push(r.fromTable);
                visited.add(r.fromTable);
              }
            });
            queue = [...new Set(next)];
            level++;
          }
          this.tables.forEach(t => { if (levels[t.id] === undefined) levels[t.id] = level; });
          return [...this.tables].sort((a, b) => {
            const la = levels[a.id] ?? 999;
            const lb = levels[b.id] ?? 999;
            if (la !== lb) return la - lb;
            return (a.name || '').localeCompare(b.name || '');
          });
        },

      };
    }
  </script>
</div>
