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

        const progress = document.createElement('div');
        progress.className = 'wrap-progress';
        const progressBar = document.createElement('div');
        progressBar.className = 'wrap-progress-bar';
        progress.appendChild(progressBar);
        container.appendChild(progress);

        let currentTrack = 0;
        let isPlaying = false;

        function highlightActive(index) {
            tracks.forEach((track, i) => {
                track.classList.toggle('active', i === index);
            });
        }

        function resetProgress() {
            progressBar.style.width = '0%';
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
            resetProgress();
            console.log('Loaded track:', url);
        }

        function playTrack() {
            if (!audio.src) {
                loadTrack(currentTrack);
            }

            const playPromise = audio.play();
            if (playPromise && typeof playPromise.then === 'function') {
                playPromise
                    .then(() => {
                        isPlaying = true;
                        playBtn.textContent = '⏸';
                    })
                    .catch((err) => {
                        console.warn('Audio play error:', err);
                    });
            } else {
                isPlaying = true;
                playBtn.textContent = '⏸';
            }
        }

        function pauseTrack() {
            audio.pause();
            isPlaying = false;
            playBtn.textContent = '▶';
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
            if (!isPlaying) {
                playTrack();
            } else {
                pauseTrack();
            }
        });

        nextBtn.addEventListener('click', nextTrack);
        prevBtn.addEventListener('click', prevTrack);

        tracks.forEach((track, index) => {
            track.addEventListener('click', () => {
                currentTrack = index;
                loadTrack(index);
                playTrack();
            });
        });

        audio.addEventListener('timeupdate', () => {
            if (!audio.duration || Number.isNaN(audio.duration)) {
                return;
            }

            const pct = (audio.currentTime / audio.duration) * 100;
            progressBar.style.width = `${pct}%`;
        });

        audio.addEventListener('ended', nextTrack);

        audio.addEventListener('play', () => {
            isPlaying = true;
            playBtn.textContent = '⏸';
        });

        audio.addEventListener('pause', () => {
            isPlaying = false;
            playBtn.textContent = '▶';
        });

        audio.addEventListener('error', () => {
            const mediaError = audio.error;
            if (mediaError) {
                const errorCode = mediaError.code;
                const errorMap = {
                    1: 'Aborted',
                    2: 'Network',
                    3: 'Decode',
                    4: 'SrcNotSupported',
                };
                const label = errorMap[errorCode] || 'Unknown';
                console.warn(
                    'Audio element encountered an error (' +
                        label +
                        '). Check audio URL and CORS configuration.',
                    mediaError
                );
            } else {
                console.warn('Audio element error. Check audio URL and CORS configuration.');
            }
        });

        loadTrack(currentTrack);
        playBtn.textContent = '▶';
    }

    document.addEventListener('DOMContentLoaded', () => {
        console.log('WRAP Player engine initializing...');
        document
            .querySelectorAll('.wrap-player-container')
            .forEach((container) => setupPlayer(container));
    });
})();
