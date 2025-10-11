/**
 * History Manager Module
 * Handles undo/redo functionality and canvas state management
 */

export class HistoryManager {
  constructor(canvasManager, layerManager) {
    this.canvasManager = canvasManager;
    this.layerManager = layerManager;
    this.history = [];
    this.historyStep = -1;
    this.isRedoing = false;
    this.isReordering = false;
    this.isLoadingDesign = false;
    this.maxHistorySteps = 50;
  }

  saveHistory() {
    const canvas = this.canvasManager.getCanvas();
    if (!canvas || this.isRedoing || this.isReordering || this.isLoadingDesign) return;

    const state = JSON.stringify(canvas.toJSON(['selectable', 'uploadId']));

    // Remove any future history if we're not at the end
    if (this.historyStep < this.history.length - 1) {
      this.history = this.history.slice(0, this.historyStep + 1);
    }

    // Add new state
    this.history.push(state);

    // Limit history size
    if (this.history.length > this.maxHistorySteps) {
      this.history = this.history.slice(-this.maxHistorySteps);
    }

    this.historyStep = this.history.length - 1;
    this.updateHistoryButtons();
  }

  undo() {
    if (this.historyStep > 0 && this.canvasManager.getCanvas()) {
      this.historyStep--;
      this.loadHistoryState(this.history[this.historyStep]);
      this.updateHistoryButtons();
    }
  }

  redo() {
    if (this.historyStep < this.history.length - 1 && this.canvasManager.getCanvas()) {
      this.historyStep++;
      this.loadHistoryState(this.history[this.historyStep]);
      this.updateHistoryButtons();
    }
  }

  loadHistoryState(stateString) {
    const canvas = this.canvasManager.getCanvas();
    if (!canvas) return;

    this.isRedoing = true;
    canvas.clear();
    this.layerManager.addFixedLayers();

    const stateToLoad = JSON.parse(stateString);

    canvas.loadFromJSON(stateToLoad, () => {
      canvas.renderAll();
      this.isRedoing = false;
      this.layerManager.ensureProperLayering();
    });
  }

  updateHistoryButtons() {
    const undoBtn = document.getElementById('undo-btn');
    const redoBtn = document.getElementById('redo-btn');

    try {
      const hasUserContentInPrevSteps = this.history.slice(0, this.historyStep).some(stateStr => {
        try {
          const state = JSON.parse(stateStr);
          return state.objects && state.objects.some(obj => obj.selectable !== false);
        } catch (error) {
          console.warn('Invalid history state:', error);
          return false;
        }
      });

      if (undoBtn) undoBtn.disabled = !hasUserContentInPrevSteps;
      if (redoBtn) redoBtn.disabled = this.historyStep >= this.history.length - 1;
    } catch (error) {
      console.error('Error updating history buttons:', error);
      if (undoBtn) undoBtn.disabled = true;
      if (redoBtn) redoBtn.disabled = true;
    }
  }

  clear() {
    this.history = [];
    this.historyStep = -1;
    this.updateHistoryButtons();
  }

  getCanHaveHistory() {
    return this.history.length > 0;
  }

  setFlags(flags) {
    if (flags.isRedoing !== undefined) this.isRedoing = flags.isRedoing;
    if (flags.isReordering !== undefined) this.isReordering = flags.isReordering;
    if (flags.isLoadingDesign !== undefined) this.isLoadingDesign = flags.isLoadingDesign;
  }
}