class MockResizeObserver {
  callback: Function
  element: any
  constructor(callback: Function) {
    this.callback = callback
  }

  observe(element: any) {
    // Simulate observation
    this.element = element
    this.trigger() // Optionally trigger it immediately for testing
  }

  unobserve() {
    // Cleanup if needed
  }

  disconnect() {
    // Disconnect observer
  }

  trigger() {
    // Trigger the callback manually, mimicking a resize event
    this.callback([
      {
        target: this.element,
        borderBoxSize: [{ inlineSize: 100, blockSize: 100 }],
        contentRect: { width: 100, height: 100 }, // Fallback properties
      },
    ])
  }
}

global.ResizeObserver = MockResizeObserver
