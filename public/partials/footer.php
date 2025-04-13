<?php

function footer(): string {
    return <<<HTML
        <footer class="bg-body-tertiary text-center">
            <div class="text-center p-3 bg-light-subtle">
                © 2025 
                <a href="https://einzwell.dev">Einzwell</a>
            </div>
        </footer>
    HTML;
}

echo footer();