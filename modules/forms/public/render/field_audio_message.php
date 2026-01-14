<?php
// Pegar configurações do Audio Message
$config = json_decode($field['config'] ?? '{}', true);
$audioUrl = $config['audio_url'] ?? '';
$waitTime = intval($config['wait_time'] ?? 0);
$buttonText = $config['button_text'] ?? 'Continuar';
$autoplay = intval($config['autoplay'] ?? 0);

// ID único para o player deste campo
$audioId = 'audio-' . $field['id'];
?>

<?php if (!empty($audioUrl)): ?>
    <div class="audio-message-container mb-6 max-w-2xl mx-auto"
         data-audio-id="<?= $audioId ?>"
         data-audio-wait="<?= $waitTime ?>"
         data-audio-autoplay="<?= $autoplay ?>">

        <!-- Player de Áudio Customizado -->
        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-6 border border-indigo-200 dark:border-indigo-800">
            <!-- Elemento de áudio oculto -->
            <audio id="<?= $audioId ?>-player" src="<?= htmlspecialchars($audioUrl) ?>" preload="metadata"></audio>

            <!-- Controles do player -->
            <div class="flex items-center gap-4 mb-4">
                <!-- Botão Play/Pause -->
                <button type="button" id="<?= $audioId ?>-play-btn"
                        class="w-14 h-14 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center hover:shadow-lg transition-all transform hover:scale-105 focus:outline-none">
                    <i class="fas fa-play text-lg"></i>
                </button>

                <!-- Tempo e Barra de Progresso -->
                <div class="flex-1">
                    <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-300 mb-1">
                        <span id="<?= $audioId ?>-current-time">0:00</span>
                        <span id="<?= $audioId ?>-duration">0:00</span>
                    </div>
                    <div class="relative h-2 bg-gray-200 dark:bg-gray-700 rounded-full cursor-pointer" id="<?= $audioId ?>-progress-bar">
                        <div id="<?= $audioId ?>-progress" class="absolute h-full bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full transition-all" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <!-- Controles adicionais -->
            <div class="flex items-center justify-between gap-4 text-sm">
                <!-- Velocidade -->
                <div class="flex items-center gap-2">
                    <i class="fas fa-tachometer-alt text-gray-500 dark:text-gray-400"></i>
                    <select id="<?= $audioId ?>-speed"
                            class="px-2 py-1 text-xs rounded bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="0.5">0.5x</option>
                        <option value="0.75">0.75x</option>
                        <option value="1" selected>1x</option>
                        <option value="1.25">1.25x</option>
                        <option value="1.5">1.5x</option>
                        <option value="2">2x</option>
                    </select>
                </div>

                <!-- Volume -->
                <div class="flex items-center gap-2 flex-1 max-w-xs">
                    <i class="fas fa-volume-up text-gray-500 dark:text-gray-400"></i>
                    <input type="range"
                           id="<?= $audioId ?>-volume"
                           min="0"
                           max="100"
                           value="100"
                           class="flex-1 h-1 bg-gray-200 dark:bg-gray-700 rounded-full appearance-none cursor-pointer"
                           style="accent-color: #6366f1;">
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
(function() {
    const audioId = '<?= $audioId ?>';
    const waitTime = <?= $waitTime ?>;
    const buttonText = <?= json_encode($buttonText) ?>;
    const autoplay = <?= $autoplay ?>;

    console.log('🎵 Audio Message inicializado:', {
        audioId: audioId,
        waitTime: waitTime,
        autoplay: autoplay
    });

    const player = document.getElementById(audioId + '-player');
    const playBtn = document.getElementById(audioId + '-play-btn');
    const progressBar = document.getElementById(audioId + '-progress-bar');
    const progress = document.getElementById(audioId + '-progress');
    const currentTimeEl = document.getElementById(audioId + '-current-time');
    const durationEl = document.getElementById(audioId + '-duration');
    const speedSelect = document.getElementById(audioId + '-speed');
    const volumeSlider = document.getElementById(audioId + '-volume');

    if (!player) {
        console.error('❌ Player de áudio não encontrado');
        return;
    }

    let isPlaying = false;

    // Formatar tempo (segundos para MM:SS)
    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return mins + ':' + (secs < 10 ? '0' : '') + secs;
    }

    // Atualizar duração quando metadados carregarem
    player.addEventListener('loadedmetadata', function() {
        durationEl.textContent = formatTime(player.duration);
        console.log('📊 Duração do áudio:', player.duration + 's');
    });

    // Atualizar progresso durante reprodução
    player.addEventListener('timeupdate', function() {
        const percent = (player.currentTime / player.duration) * 100;
        progress.style.width = percent + '%';
        currentTimeEl.textContent = formatTime(player.currentTime);
    });

    // Play/Pause
    playBtn.addEventListener('click', function() {
        if (isPlaying) {
            player.pause();
            playBtn.innerHTML = '<i class="fas fa-play text-lg"></i>';
            isPlaying = false;
            console.log('⏸️ Áudio pausado');
        } else {
            player.play();
            playBtn.innerHTML = '<i class="fas fa-pause text-lg"></i>';
            isPlaying = true;
            console.log('▶️ Áudio reproduzindo');
        }
    });

    // Quando áudio terminar
    player.addEventListener('ended', function() {
        playBtn.innerHTML = '<i class="fas fa-play text-lg"></i>';
        isPlaying = false;
        progress.style.width = '0%';
        console.log('✅ Áudio finalizado');
    });

    // Click na barra de progresso
    progressBar.addEventListener('click', function(e) {
        const rect = progressBar.getBoundingClientRect();
        const percent = (e.clientX - rect.left) / rect.width;
        player.currentTime = percent * player.duration;
        console.log('⏩ Áudio avançado para:', player.currentTime + 's');
    });

    // Controle de velocidade
    speedSelect.addEventListener('change', function() {
        player.playbackRate = parseFloat(this.value);
        console.log('⚡ Velocidade alterada para:', this.value + 'x');
    });

    // Controle de volume
    volumeSlider.addEventListener('input', function() {
        player.volume = this.value / 100;
    });

    // Função para bloquear o botão com temporizador
    function blockButton() {
        const audioContainer = document.querySelector('[data-audio-id="' + audioId + '"]');
        if (!audioContainer) {
            console.error('❌ Audio container não encontrado:', audioId);
            return;
        }

        const slide = audioContainer.closest('.question-slide');
        if (!slide) {
            console.error('❌ Slide não encontrado');
            return;
        }

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

        console.log('✅ Botão encontrado:', button);

        // Desabilitar botão inicialmente
        button.disabled = true;
        button.classList.add('opacity-50', 'cursor-not-allowed');
        button.setAttribute('data-audio-blocked', 'true');

        const originalButtonHTML = button.innerHTML;

        // Bloquear tecla Enter
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

                button.disabled = false;
                button.classList.remove('opacity-50', 'cursor-not-allowed');
                button.removeAttribute('data-audio-blocked');
                button.innerHTML = buttonText;

                document.removeEventListener('keydown', enterBlocker, true);
                slide.removeEventListener('keydown', enterBlocker, true);

                console.log('✅ Áudio liberado! Botão habilitado');
            }
        }, 1000);
    }

    // Função para autoplay
    function startAutoplay() {
        if (!autoplay) {
            console.log('⏸️ Autoplay desativado');
            return;
        }

        console.log('▶️ Iniciando autoplay...');
        player.play();
        playBtn.innerHTML = '<i class="fas fa-pause text-lg"></i>';
        isPlaying = true;
        console.log('✅ Autoplay iniciado');
    }

    // Função para inicializar quando o slide ficar visível
    function initWhenVisible() {
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

        const isVisible = slide.style.display !== 'none';
        console.log('👁️ Slide visível?', isVisible);

        if (isVisible) {
            console.log('🚀 Slide já visível, iniciando...');

            if (waitTime > 0) {
                setTimeout(blockButton, 300);
            }

            if (autoplay) {
                setTimeout(startAutoplay, 800);
            }
        } else {
            console.log('⏳ Aguardando slide ficar visível...');

            const observer = new MutationObserver(function(mutations) {
                const nowVisible = slide.style.display !== 'none';

                if (nowVisible) {
                    console.log('✅ Slide ficou visível!');
                    observer.disconnect();

                    if (waitTime > 0) {
                        setTimeout(blockButton, 300);
                    }

                    if (autoplay) {
                        setTimeout(startAutoplay, 800);
                    }
                }
            });

            observer.observe(slide, {
                attributes: true,
                attributeFilter: ['style']
            });
        }
    }

    // Inicializar quando o DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWhenVisible);
    } else {
        setTimeout(initWhenVisible, 100);
    }
})();
</script>
