<?php
// Pegar configurações do VSL
$config = json_decode($field['config'] ?? '{}', true);
$videoUrl = $config['video_url'] ?? '';
$waitTime = intval($config['wait_time'] ?? 0);
$buttonText = $config['button_text'] ?? 'Continuar';
$autoplay = intval($config['autoplay'] ?? 0);

// Processar URL do vídeo
$embedUrl = '';
if (!empty($videoUrl)) {
    // YouTube
    if (strpos($videoUrl, 'youtube.com') !== false || strpos($videoUrl, 'youtu.be') !== false) {
        $videoId = '';
        if (strpos($videoUrl, 'youtu.be/') !== false) {
            $videoId = explode('youtu.be/', $videoUrl)[1];
            $videoId = explode('?', $videoId)[0];
        } elseif (strpos($videoUrl, 'youtube.com/watch?v=') !== false) {
            parse_str(parse_url($videoUrl, PHP_URL_QUERY), $params);
            $videoId = $params['v'] ?? '';
        }
        if ($videoId) {
            $embedUrl = "https://www.youtube.com/embed/" . htmlspecialchars($videoId);
            // Adicionar parâmetros de autoplay se ativado
            if ($autoplay) {
                $embedUrl .= "?autoplay=1&mute=0&enablejsapi=1";
            }
        }
    }
    // Vimeo
    elseif (strpos($videoUrl, 'vimeo.com') !== false) {
        if (preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $matches)) {
            $embedUrl = "https://player.vimeo.com/video/" . htmlspecialchars($matches[1]);
            // Adicionar parâmetros de autoplay se ativado
            if ($autoplay) {
                $embedUrl .= "?autoplay=1&muted=0";
            }
        }
    }
}

// ID único para o botão deste campo
$vslId = 'vsl-' . $field['id'];
?>

<?php if (!empty($embedUrl)): ?>
    <div class="media-container mb-6 aspect-video max-w-4xl mx-auto"
         data-vsl-id="<?= $vslId ?>"
         data-vsl-wait="<?= $waitTime ?>"
         data-vsl-autoplay="<?= $autoplay ?>">
        <iframe id="<?= $vslId ?>-iframe"
                class="w-full h-full rounded-lg border border-gray-200 dark:border-zinc-700"
                src="<?= $embedUrl ?>"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen>
        </iframe>
    </div>
<?php endif; ?>

<script>
(function() {
    const vslId = '<?= $vslId ?>';
    const waitTime = <?= $waitTime ?>;
    const buttonText = <?= json_encode($buttonText) ?>;
    const autoplay = <?= $autoplay ?>;

    console.log('🎬 VSL inicializado:', vslId, 'Wait time:', waitTime, 'Autoplay:', autoplay);

    // Função para bloquear o botão com temporizador
    function blockButton() {
        const vslContainer = document.querySelector('[data-vsl-id="' + vslId + '"]');
        if (!vslContainer) {
            console.error('❌ VSL container não encontrado:', vslId);
            return;
        }

        // Encontrar o slide que contém o VSL
        const slide = vslContainer.closest('.question-slide');
        if (!slide) {
            console.error('❌ Slide não encontrado');
            return;
        }

        // Buscar o botão dentro da div de navegação
        const navigationDiv = slide.querySelector('.flex.items-center.gap-4');
        if (!navigationDiv) {
            console.error('❌ Div de navegação não encontrada');
            return;
        }

        // Encontrar o botão de avançar (não é submit)
        const button = navigationDiv.querySelector('button[type="button"]');
        if (!button) {
            console.error('❌ Botão de avançar não encontrado');
            return;
        }

        console.log('✅ Botão encontrado:', button);

        // Desabilitar botão inicialmente
        button.disabled = true;
        button.classList.add('opacity-50', 'cursor-not-allowed');

        // Adicionar atributo para identificar que está bloqueado pelo VSL
        button.setAttribute('data-vsl-blocked', 'true');

        // Bloquear tecla Enter
        const enterBlocker = function(e) {
            if (e.key === 'Enter' && button.getAttribute('data-vsl-blocked') === 'true') {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        };
        slide.addEventListener('keydown', enterBlocker, true);

        let timeLeft = waitTime;

        // Atualizar texto do botão
        function updateButtonText() {
            button.innerHTML = 'Aguarde <span class="font-bold">' + timeLeft + 's</span>';
        }

        updateButtonText();
        console.log('⏱️ Contador iniciado:', timeLeft, 'segundos');

        // Contador regressivo
        const countdown = setInterval(function() {
            timeLeft--;

            if (timeLeft > 0) {
                updateButtonText();
            } else {
                clearInterval(countdown);

                // Habilitar botão
                button.disabled = false;
                button.classList.remove('opacity-50', 'cursor-not-allowed');
                button.removeAttribute('data-vsl-blocked');

                // Usar texto customizado
                button.innerHTML = buttonText;

                // Remover bloqueio de Enter
                slide.removeEventListener('keydown', enterBlocker, true);

                console.log('✅ VSL liberado, botão habilitado');
            }
        }, 1000);
    }

    // Se tiver tempo de espera, iniciar bloqueio quando o slide estiver visível
    if (waitTime > 0) {
        // Aguardar o DOM estar pronto
        function init() {
            // Verificar se o slide já está visível (é o primeiro slide)
            const vslContainer = document.querySelector('[data-vsl-id="' + vslId + '"]');
            if (vslContainer) {
                const slide = vslContainer.closest('.question-slide');
                if (slide && slide.style.display !== 'none') {
                    // Slide já está visível, iniciar bloqueio imediatamente
                    setTimeout(blockButton, 500);
                } else {
                    // Slide não está visível ainda, observar quando ficar visível
                    const observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                                const slide = mutation.target;
                                if (slide.style.display !== 'none') {
                                    observer.disconnect();
                                    setTimeout(blockButton, 500);
                                }
                            }
                        });
                    });

                    if (slide) {
                        observer.observe(slide, { attributes: true });
                    }
                }
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    }
})();
</script>
