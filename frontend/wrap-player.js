(function () {
    function setupPlayer(container) {
        const audio = container.querySelector('audio');
        const tracks = Array.from(container.querySelectorAll('.wrap-track-item'));
        const playBtn = container.querySelector('.wrap-play');
        const nextBtn = container.querySelector('.wrap-next');
        const prevBtn = container.querySelector('.wrap-prev');

        if (!audio || !tracks.length || !playBtn || !nextBtn || !prevBtn) {
            return;
        }

        let currentTrack = 0;

        function highlightActive(index) {
            tracks.forEach((track, i) => {
                track.classList.toggle('active', i === index);
            });
        }

        function loadTrack(index) {
            const track = tracks[index];
            if (!track) {
                return;
            }

            const url = track.dataset.url;
            if (!url) {
                return;
            }

            audio.src = url;

            currentTrack = index;
            highlightActive(index);
        }

        function playTrack() {
            if (!audio.src) {
                loadTrack(currentTrack);
            }

            const playPromise = audio.play();
            if (playPromise && typeof playPromise.then === 'function') {
                playPromise.catch((err) => {
                    console.warn('Unable to play track', err);
                });
            }
        }

        function pauseTrack() {
            audio.pause();
        }

        function nextTrack() {
            const nextIndex = (currentTrack + 1) % tracks.length;
            loadTrack(nextIndex);
            playTrack();
        }

        function prevTrack() {
            const prevIndex = (currentTrack - 1 + tracks.length) % tracks.length;
            loadTrack(prevIndex);
            playTrack();
        }

        playBtn.addEventListener('click', () => {
            if (audio.paused) {
                playTrack();
            } else {
                pauseTrack();
            }
        });

        nextBtn.addEventListener('click', nextTrack);
        prevBtn.addEventListener('click', prevTrack);

        tracks.forEach((track, index) => {
            track.addEventListener('click', () => {
                loadTrack(index);
                playTrack();
            });
        });

        audio.addEventListener('ended', nextTrack);
        audio.addEventListener('play', () => {
            playBtn.textContent = '⏸';
        });
        audio.addEventListener('pause', () => {
            playBtn.textContent = '▶';
        });

        loadTrack(currentTrack);
        playBtn.textContent = '▶';
    }

    document.addEventListener('DOMContentLoaded', () => {
        document
            .querySelectorAll('.wrap-player-container')
            .forEach((container) => setupPlayer(container));
    });
})();
