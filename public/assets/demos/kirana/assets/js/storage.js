(function (window) {
  "use strict";

  const memory = new Map();
  const available = (() => {
    try {
      const key = "__fb_storage_test__";
      window.localStorage.setItem(key, "1");
      window.localStorage.removeItem(key);
      return true;
    } catch (error) {
      return false;
    }
  })();

  function read(key, fallback) {
    try {
      const raw = available ? window.localStorage.getItem(key) : memory.get(key);
      return raw == null ? fallback : JSON.parse(raw);
    } catch (error) {
      return fallback;
    }
  }

  function write(key, value) {
    try {
      const raw = JSON.stringify(value);
      if (available) window.localStorage.setItem(key, raw);
      else memory.set(key, raw);
      return true;
    } catch (error) {
      return false;
    }
  }

  function remove(key) {
    try {
      if (available) window.localStorage.removeItem(key);
      else memory.delete(key);
      return true;
    } catch (error) {
      return false;
    }
  }

  function update(key, updater, fallback) {
    const next = updater(read(key, fallback));
    write(key, next);
    return next;
  }

  window.FBStorage = Object.freeze({
    available,
    get: read,
    set: write,
    remove,
    update,
    clearNamespace(prefix) {
      if (!available) {
        [...memory.keys()].filter((key) => key.startsWith(prefix)).forEach((key) => memory.delete(key));
        return;
      }
      Object.keys(window.localStorage)
        .filter((key) => key.startsWith(prefix))
        .forEach((key) => window.localStorage.removeItem(key));
    }
  });
})(window);

