<?php
// Pegar configurações do Audio Message
$config = json_decode($field['config'] ?? '{}', true);
$audioUrl = $config['audio_url'] ?? '';
$waitTime = intval($config['wait_time'] ?? 0);
$buttonText = $config['button_text'] ?? 'Continuar';

// ID único para este campo
$audioId = 'audio-' . $field['id'];
?>

<?php if (!empty($audioUrl)): ?>
    <div class="audio-message-container mb-6 max-w-4xl mx-auto"
         data-audio-id="<?= $audioId ?>"
         data-audio-wait="<?= $waitTime ?>">

        <!-- Custom Audio Player -->
        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-2xl p-6 shadow-lg">

            <!-- Audio Element (hidden) -->
            <audio id="<?= $audioId ?>-element" preload="metadata">
                <source src="<?= htmlspecialchars($audioUrl) ?>" type="audio/mpeg">
                Seu navegador não suporta áudio.
            </audio>

            <!-- Play/Pause Button -->
            <div class="flex items-center justify-center mb-6">
                <button type="button"
                        id="<?= $audioId ?>-play-btn"
                        class="play-pause-btn w-20 h-20 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center transform hover:scale-105">
                    <i class="fas fa-play text-3xl ml-1"></i>
                </button>
            </div>

            <!-- Progress Bar -->
            <div class="mb-4">
                <div class="relative h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden cursor-pointer"
                     id="<?= $audioId ?>-progress-container">
                    <div id="<?= $audioId ?>-progress-bar"
                         class="absolute top-0 left-0 h-full bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full transition-all duration-100"
                         style="width: 0%"></div>
                </div>
            </div>

            <!-- Time Display and Controls -->
            <div class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-4">
                    <span id="<?= $audioId ?>-current-time" class="text-gray-700 dark:text-gray-300 font-medium">0:00</span>
                    <span class="text-gray-400">/</span>
                    <span id="<?= $audioId ?>-duration" class="text-gray-500 dark:text-gray-400">0:00</span>
                </div>

                <!-- Speed Control -->
                <div class="flex items-center gap-2">
                    <i class="fas fa-tachometer-alt text-gray-500"></i>
                    <select id="<?= $audioId ?>-speed"
                            class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="0.5">0.5x</option>
                        <option value="0.75">0.75x</option>
                        <option value="1" selected>1x</option>
                        <option value="1.25">1.25x</option>
                        <option value="1.5">1.5x</option>
                        <option value="1.75">1.75x</option>
                        <option value="2">2x</option>
                    </select>
                </div>
            </div>

            <!-- Volume Control (opcional, pode adicionar depois) -->
            <div class="mt-4 flex items-center gap-3">
                <i class="fas fa-volume-up text-gray-500"></i>
                <input type="range"
                       id="<?= $audioId ?>-volume"
                       min="0"
                       max="100"
                       value="100"
                       class="flex-1 h-1 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-indigo-600">
            </div>
        </div>
    </div>

    <script>
    (function() {
        const audioId = '<?= $audioId ?>';
        const waitTime = <?= $waitTime ?>;
        const buttonText = <?= json_encode($buttonText) ?>;

        console.log('🎵 Audio Message inicializado:', {
            audioId: audioId,
            waitTime: waitTime
        });

        // Elementos
        const audio = document.getElementById(audioId + '-element');
        const playBtn = document.getElementById(audioId + '-play-btn');
        const progressContainer = document.getElementById(audioId + '-progress-container');
        const progressBar = document.getElementById(audioId + '-progress-bar');
        const currentTimeEl = document.getElementById(audioId + '-current-time');
        const durationEl = document.getElementById(audioId + '-duration');
        const speedSelect = document.getElementById(audioId + '-speed');
        const volumeSlider = document.getElementById(audioId + '-volume');

        let isPlaying = false;

        // Formatar tempo (segundos para MM:SS)
        function formatTime(seconds) {
            if (isNaN(seconds)) return '0:00';
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return mins + ':' + (secs < 10 ? '0' : '') + secs;
        }

        // Quando o áudio estiver carregado
        audio.addEventListener('loadedmetadata', function() {
            durationEl.textContent = formatTime(audio.duration);
            console.log('✅ Áudio carregado, duração:', audio.duration + 's');
        });

        // Atualizar progresso
        audio.addEventListener('timeupdate', function() {
            const progress = (audio.currentTime / audio.duration) * 100;
            progressBar.style.width = progress + '%';
            currentTimeEl.textContent = formatTime(audio.currentTime);
        });

        // Play/Pause
        playBtn.addEventListener('click', function() {
            if (isPlaying) {
                audio.pause();
                playBtn.innerHTML = '<i class="fas fa-play text-3xl ml-1"></i>';
                isPlaying = false;
                console.log('⏸️ Áudio pausado');
            } else {
                audio.play();
                playBtn.innerHTML = '<i class="fas fa-pause text-3xl"></i>';
                isPlaying = true;
                console.log('▶️ Áudio tocando');
            }
        });

        // Quando o áudio terminar
        audio.addEventListener('ended', function() {
            playBtn.innerHTML = '<i class="fas fa-play text-3xl ml-1"></i>';
            isPlaying = false;
            console.log('✅ Áudio finalizado');
        });

        // Click na barra de progresso para navegar
        progressContainer.addEventListener('click', function(e) {
            const rect = progressContainer.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            audio.currentTime = percent * audio.duration;
            console.log('⏩ Navegado para:', formatTime(audio.currentTime));
        });

        // Controle de velocidade
        speedSelect.addEventListener('change', function() {
            audio.playbackRate = parseFloat(this.value);
            console.log('⚡ Velocidade alterada para:', this.value + 'x');
        });

        // Controle de volume
        volumeSlider.addEventListener('input', function() {
            audio.volume = this.value / 100;
        });

        // ==================== BLOQUEIO DO BOTÃO ====================

        function blockButton() {
            const audioContainer = document.querySelector('[data-audio-id="' + audioId + '"]');
            if (!audioContainer) {
                console.error('❌ Audio container não encontrado');
                return;
            }

            const slide = audioContainer.closest('.question-slide');
            if (!slide) {
                console.error('❌ Slide não encontrado');
                return;
            }

            console.log('✅ Slide encontrado');

            const navigationDiv = slide.querySelector('.flex.items-center.gap-4');
            if (!navigationDiv) {
                console.error('❌ Div de navegação não encontrada');
                return;
            }

            const button = navigationDiv.querySelector('button[type="button"]');
            if (!button) {
                console.error('❌ Botão de avançar não encontrado');
                return;
            }

            console.log('✅ Botão encontrado');

            // Desabilitar botão
            button.disabled = true;
            button.classList.add('opacity-50', 'cursor-not-allowed');
            button.setAttribute('data-audio-blocked', 'true');

            // Bloquear Enter
            const enterBlocker = function(e) {
                if (e.key === 'Enter' && button.hasAttribute('data-audio-blocked')) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    console.log('⛔ Enter bloqueado - aguarde o timer');
                    return false;
                }
            };

            document.addEventListener('keydown', enterBlocker, true);
            slide.addEventListener('keydown', enterBlocker, true);

            let timeLeft = waitTime;

            function updateButtonText() {
                button.innerHTML = 'Aguarde <span class="font-bold">' + timeLeft + 's</span>';
            }

            updateButtonText();
            console.log('⏱️ Contador iniciado:', timeLeft, 'segundos');

            const countdown = setInterval(function() {
                timeLeft--;
                console.log('⏱️ Timer:', timeLeft + 's restantes');

                if (timeLeft > 0) {
                    updateButtonText();
                } else {
                    clearInterval(countdown);

                    // Habilitar botão
                    button.disabled = false;
                    button.classList.remove('opacity-50', 'cursor-not-allowed');
                    button.removeAttribute('data-audio-blocked');
                    button.innerHTML = buttonText;

                    // Remover bloqueios
                    document.removeEventListener('keydown', enterBlocker, true);
                    slide.removeEventListener('keydown', enterBlocker, true);

                    console.log('✅ Áudio liberado! Botão habilitado');
                }
            }, 1000);
        }

        // Inicializar bloqueio quando o slide ficar visível
        if (waitTime > 0) {
            function initWhenVisible() {
                const audioContainer = document.querySelector('[data-audio-id="' + audioId + '"]');
                if (!audioContainer) return;

                const slide = audioContainer.closest('.question-slide');
                if (!slide) return;

                const isVisible = slide.style.display !== 'none';
                console.log('👁️ Slide visível?', isVisible);

                if (isVisible) {
                    console.log('🚀 Slide já visível, iniciando bloqueio...');
                    setTimeout(blockButton, 300);
                } else {
                    console.log('⏳ Aguardando slide ficar visível...');
                    const observer = new MutationObserver(function(mutations) {
                        const nowVisible = slide.style.display !== 'none';
                        if (nowVisible) {
                            console.log('✅ Slide ficou visível!');
                            observer.disconnect();
                            setTimeout(blockButton, 300);
                        }
                    });

                    observer.observe(slide, {
                        attributes: true,
                        attributeFilter: ['style']
                    });
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initWhenVisible);
            } else {
                setTimeout(initWhenVisible, 100);
            }
        }
    })();
    </script>
<?php else: ?>
    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
        <p class="text-yellow-800 dark:text-yellow-200 text-sm">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Nenhum áudio foi configurado para este campo.
        </p>
    </div>
<?php endif; ?>
