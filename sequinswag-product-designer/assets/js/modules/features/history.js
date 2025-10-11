/**
 * Undo/redo functionality
 * Part of Enhanced Product Designer
 * 
 * @module historyMethods
 */

export const historyMethods = {


saveHistory() {
  const currentObjects = this.canvas.getObjects().filter(obj => obj.selectable !== false);
  const currentState = JSON.stringify({objects: currentObjects.map(obj => obj.toObject(['selectable', 'evented', 'crossOrigin']))});

  if (this.isRedoing || this.isReordering || this.isLoadingDesign) return;

  if (this.historyStep >= 0 && this.history[this.historyStep] === currentState) {
    return;
  }

  this.history = this.history.slice(0, this.historyStep + 1);
  this.history.push(currentState);
  this.historyStep++;
  if (this.history.length > 50) {
    this.history.shift();
    this.historyStep--;
  }
  this.updateHistoryButtons();
  this.saveSessionDesign();
},



undo() {
  if (this.historyStep > 0 && this.canvas) {
    this.historyStep--;
    this.isRedoing = true;
    this.canvas.clear();
    this.addFixedLayers();

    const stateToLoad = JSON.parse(this.history[this.historyStep]);

    this.canvas.loadFromJSON(stateToLoad, () => {
      this.canvas.renderAll();
      this.isRedoing = false;
      this.updateHistoryButtons();
      this.ensureProperLayering();
    }, (o, object) => {
      // Custom revive logic if needed
    });
  }
},



redo() {
  if (this.historyStep < this.history.length - 1 && this.canvas) {
    this.historyStep++;
    this.isRedoing = true;
    this.canvas.clear();
    this.addFixedLayers();

    const stateToLoad = JSON.parse(this.history[this.historyStep]);

    this.canvas.loadFromJSON(stateToLoad, () => {
      this.canvas.renderAll();
      this.isRedoing = false;
      this.updateHistoryButtons();
      this.ensureProperLayering();
    }, (o, object) => {
      // Custom revive logic if needed
    });
  }
},



updateHistoryButtons() {
  const undoBtn = document.getElementById('undo-btn');
  const redoBtn = document.getElementById('redo-btn');

  const hasUserContentInPrevSteps = this.history.slice(0, this.historyStep).some(stateStr => {
    const state = JSON.parse(stateStr);
    return state.objects.some(obj => obj.selectable !== false);
  });

  if (undoBtn) undoBtn.disabled = !hasUserContentInPrevSteps;
  if (redoBtn) redoBtn.disabled = this.historyStep >= this.history.length - 1;
}
};
