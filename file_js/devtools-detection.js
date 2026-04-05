function checkDevtools(callback) {
  const threshold = 160;
  let devtoolsDetected = false;

  function checkDevtoolsWindowSize() {
    const widthThreshold = window.outerWidth - window.innerWidth > threshold;
    const heightThreshold = window.outerHeight - window.innerHeight > threshold;

    if (
      (widthThreshold && window.innerWidth > window.screen.width) ||
      (heightThreshold && window.innerHeight > window.screen.height)
    ) {
      devtoolsDetected = true;
    } else {
      devtoolsDetected = false;
    }
  }

  function checkDevtoolsTiming() {
    const startTime = performance.now();
    debugger;
    const endTime = performance.now();

    if (endTime - startTime > 100) {
      devtoolsDetected = true;
    }
  }

  function checkDevtoolsProperty() {
    if (window.devtools) {
      devtoolsDetected = true;
    }
  }

  checkDevtoolsWindowSize();
  checkDevtoolsTiming();
  checkDevtoolsProperty();

  callback(devtoolsDetected);
}