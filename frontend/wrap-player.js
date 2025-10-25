(() => {
  const PLAYER_SELECTOR = ".wrap-player-container";
  const STORAGE_PREFIX = "wrap_last_";
  const INITIALIZED_ATTR = "wrapInitialized";
  const RESUME_TOAST_THRESHOLD = 2;

  const toAbsoluteUrl = (() => {
    const anchor = document.createElement("a");
    return (url) => {
      if (!url) {
        return "";
      }
      anchor.href = url;
      return anchor.href;
    };
  })();

  const storageKeyFor = (playerId) => `${STORAGE_PREFIX}${playerId}`;

  const storage = {
    save(playerId, payload) {
      if (!playerId) {
        return;
      }
      try {
        localStorage.setItem(storageKeyFor(playerId), JSON.stringify(payload));
      } catch (error) {
        // Storage can fail in private mode; ignore silently.
      }
    },
    load(playerId) {
      if (!playerId) {
        return null;
      }
      try {
        const raw = localStorage.getItem(storageKeyFor(playerId));
        return raw ? JSON.parse(raw) : null;
      } catch (error) {
        return null;
      }
    },
    clear(playerId) {
      if (!playerId) {
        return;
      }
      try {
        localStorage.removeItem(storageKeyFor(playerId));
      } catch (error) {
        // Ignore when storage is unavailable.
      }
    },
  };

  const clampPercentage = (value) => Math.max(0, Math.min(100, value));

  const formatTime = (value, fallback = "--:--") => {
    if (!Number.isFinite(value) || value < 0) {
      return fallback;
    }

    const totalSeconds = Math.floor(value);
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return `${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
  };

  const showToast = (root, message) => {
    if (!root) {
      return;
    }

    const previous = root.querySelector(".wrap-toast");
    if (previous) {
      previous.remove();
    }

    const toast = document.createElement("div");
    toast.className = "wrap-toast";
    toast.textContent = message;
    root.appendChild(toast);

    window.setTimeout(() => {
      toast.remove();
    }, 2600);
  };

  const bootstrapPlayers = () => {
    const players = document.querySelectorAll(PLAYER_SELECTOR);
    if (!players.length) {
      return;
    }

    players.forEach((root) => {
      if (!(root instanceof HTMLElement)) {
        return;
      }

      if (root.dataset[INITIALIZED_ATTR] === "1") {
        return;
      }

      const audio = root.querySelector("audio");
      const trackNodes = Array.from(root.querySelectorAll(".wrap-track-item"));

      if (!(audio instanceof HTMLAudioElement) || !trackNodes.length) {
        return;
      }

      root.dataset[INITIALIZED_ATTR] = "1";

      const playerId = root.dataset.playerId || "";
      const controls = root.querySelector(".wrap-controls");
      const playBtn = root.querySelector(".wrap-play");
      const nextBtn = root.querySelector(".wrap-next");
      const prevBtn = root.querySelector(".wrap-prev");

      let progressWrap = root.querySelector(".wrap-progress, .wrap-player-progress");
      if (!progressWrap) {
        progressWrap = document.createElement("div");
        progressWrap.className = "wrap-progress wrap-player-progress";
        progressWrap.setAttribute("role", "presentation");

        if (controls) {
          controls.insertAdjacentElement("afterend", progressWrap);
        } else {
          root.appendChild(progressWrap);
        }
      } else {
        if (!progressWrap.classList.contains("wrap-progress")) {
          progressWrap.classList.add("wrap-progress");
        }
        if (!progressWrap.classList.contains("wrap-player-progress")) {
          progressWrap.classList.add("wrap-player-progress");
        }
        if (!progressWrap.hasAttribute("role")) {
          progressWrap.setAttribute("role", "presentation");
        }
      }

      let progressFill = progressWrap.querySelector(".wrap-progress-bar");
      if (!progressFill) {
        progressFill = progressWrap.querySelector("span, div");
      }
      if (!progressFill) {
        progressFill = document.createElement("div");
        progressWrap.appendChild(progressFill);
      }

      progressFill.classList.add("wrap-progress-bar");

      let timeDisplay = root.querySelector(".wrap-player-time");
      if (!timeDisplay) {
        timeDisplay = document.createElement("div");
        timeDisplay.className = "wrap-player-time";
        timeDisplay.textContent = "00:00 / --:--";
        if (progressWrap && progressWrap.parentNode) {
          progressWrap.insertAdjacentElement("afterend", timeDisplay);
        } else {
          root.appendChild(timeDisplay);
        }
      }

      let currentIndex = 0;

      const setActiveTrack = (index) => {
        trackNodes.forEach((node, position) => {
          node.classList.toggle("active", position === index);
        });
      };

      const findIndexByUrl = (url) => {
        if (!url) {
          return -1;
        }
        const target = toAbsoluteUrl(url);
        return trackNodes.findIndex((node) => toAbsoluteUrl(node.dataset.url) === target);
      };

      const syncProgressFill = () => {
        if (!progressFill) {
          return;
        }

        if (!Number.isFinite(audio.duration) || audio.duration <= 0) {
          progressFill.style.width = "0%";
          return;
        }

        const percentage = (audio.currentTime / audio.duration) * 100;
        progressFill.style.width = `${clampPercentage(percentage)}%`;
      };

      const syncTimeDisplay = () => {
        if (!timeDisplay) {
          return;
        }

        const currentText = formatTime(audio.currentTime, "00:00");
        const durationText = Number.isFinite(audio.duration) && audio.duration > 0
          ? formatTime(audio.duration)
          : "--:--";

        timeDisplay.textContent = `${currentText} / ${durationText}`;
      };

      const updatePlaybackUi = () => {
        syncProgressFill();
        syncTimeDisplay();
      };

      const queueTrack = (index, options = {}) => {
        if (!trackNodes[index]) {
          return false;
        }

        const url = trackNodes[index].dataset.url;
        if (!url) {
          return false;
        }

        currentIndex = index;
        audio.pause();

        const resumeFrom = Number.isFinite(options.resumeFrom) ? options.resumeFrom : null;

        audio.src = url;
        audio.currentTime = 0;
        setActiveTrack(index);
        if (progressFill) {
          progressFill.style.width = "0%";
        }
        syncTimeDisplay();

        if (Number.isFinite(resumeFrom) && resumeFrom > 0) {
          const applyResume = () => {
            if (audio.duration && resumeFrom < audio.duration) {
              audio.currentTime = resumeFrom;
              updatePlaybackUi();
            }
          };
          audio.addEventListener("loadedmetadata", applyResume, { once: true });
        }

        return true;
      };

      const playTrack = () => {
        audio
          .play()
          .catch(() => {
            // Autoplay or permission block; leave button state unchanged.
          });
      };

      const goToNext = () => {
        const nextIndex = (currentIndex + 1) % trackNodes.length;
        if (queueTrack(nextIndex)) {
          playTrack();
        }
      };

      const goToPrev = () => {
        const prevIndex = (currentIndex - 1 + trackNodes.length) % trackNodes.length;
        if (queueTrack(prevIndex)) {
          playTrack();
        }
      };

      if (playBtn) {
        playBtn.addEventListener("click", () => {
          if (audio.paused) {
            playTrack();
          } else {
            audio.pause();
          }
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener("click", goToNext);
      }

      if (prevBtn) {
        prevBtn.addEventListener("click", goToPrev);
      }

      trackNodes.forEach((node, index) => {
        node.addEventListener("click", () => {
          if (queueTrack(index)) {
            playTrack();
          }
        });
      });

      if (progressWrap) {
        progressWrap.addEventListener("click", (event) => {
          if (!Number.isFinite(audio.duration) || audio.duration <= 0) {
            return;
          }
          const rect = progressWrap.getBoundingClientRect();
          if (!rect.width) {
            return;
          }
          const ratio = (event.clientX - rect.left) / rect.width;
          const clampedRatio = Math.max(0, Math.min(1, ratio));
          const nextTime = clampedRatio * audio.duration;
          audio.currentTime = nextTime;
          updatePlaybackUi();
        });
      }

      audio.addEventListener("play", () => {
        if (playBtn) {
          playBtn.textContent = "⏸";
        }
      });

      audio.addEventListener("pause", () => {
        if (playBtn) {
          playBtn.textContent = "▶";
        }
      });

      audio.addEventListener("timeupdate", () => {
        updatePlaybackUi();

        const src = audio.currentSrc || audio.src;
        if (src) {
          storage.save(playerId, {
            src,
            time: audio.currentTime,
          });
        }
      });

      audio.addEventListener("seeked", updatePlaybackUi);

      audio.addEventListener("loadedmetadata", () => {
        updatePlaybackUi();
      });

      audio.addEventListener("ended", () => {
        storage.clear(playerId);
        if (progressFill) {
          progressFill.style.width = "0%";
        }
        syncTimeDisplay();
        goToNext();
      });

      audio.addEventListener("emptied", () => {
        if (progressFill) {
          progressFill.style.width = "0%";
        }
        syncTimeDisplay();
      });

      audio.addEventListener("error", () => {
        storage.clear(playerId);
        if (progressFill) {
          progressFill.style.width = "0%";
        }
        syncTimeDisplay();
      });

      const saved = storage.load(playerId);
      if (saved && saved.src) {
        const savedIndex = findIndexByUrl(saved.src);
        const resumeTime = Number(saved.time);
        if (savedIndex !== -1) {
          const resumeFrom = Number.isFinite(resumeTime) ? resumeTime : null;
          queueTrack(savedIndex, { resumeFrom });
          if (Number.isFinite(resumeTime) && resumeTime > RESUME_TOAST_THRESHOLD) {
            showToast(root, "Continuing from last position...");
          }
        } else {
          storage.clear(playerId);
          queueTrack(0);
        }
      } else {
        queueTrack(0);
      }

      updatePlaybackUi();
    });
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bootstrapPlayers);
  } else {
    bootstrapPlayers();
  }

  if (typeof window !== "undefined") {
    window.WRAP = window.WRAP || {};
    window.WRAP.bootstrapPlayers = bootstrapPlayers;
  }
})();
