<style>
    <?php
    use Services\Debug\DebugService;

    echo file_get_contents(__DIR__ . '/debug.css');
    ?>
</style>
<nav id="debug" class="navbar bg-dark navbar-fixed-bottom">
    <div class="d-flex">
        <a target="_blank" class="navbar-brand" href="/Debugbar/debug.vue.php"><?php echo strtoupper(getEnv("APP_NAME")) ?>
            DEBUG</a>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            <div class="navbar-nav d-flex justify-content-between">
                <div class="d-flex ">
                    <a target="_blank" class="nav-item nav-link active" href="/Debugbar/debug.vue.php?section=server">
                        <svg height="0.75em" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="fa" viewBox="0 0 512 512">
                            <path d="M0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zM288 96a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zM256 416c35.3 0 64-28.7 64-64c0-17.4-6.9-33.1-18.1-44.6L366 161.7c5.3-12.1-.2-26.3-12.3-31.6s-26.3 .2-31.6 12.3L257.9 288c-.6 0-1.3 0-1.9 0c-35.3 0-64 28.7-64 64s28.7 64 64 64zM176 144a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zM96 288a32 32 0 1 0 0-64 32 32 0 1 0 0 64zm352-32a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z" />
                        </svg>
                        <span> <?= $_SESSION["DEBUG"]["SERVER"]["REQUEST_TIME"] ?></span>
                    </a>
                    <a target="_blank" class="nav-item nav-link active" style="<?= DebugService::getStyleStatus($_SESSION["DEBUG"]["SERVER"]["HTTP_STATUS"]) ?>padding-top: 1px;" href="/Debugbar/debug.vue.php?section=server">
                        <svg height="0.75em" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="fa" viewBox="0 0 512 512">
                            <path d="M64 32C28.7 32 0 60.7 0 96v64c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zm280 72a24 24 0 1 1 0 48 24 24 0 1 1 0-48zm48 24a24 24 0 1 1 48 0 24 24 0 1 1 -48 0zM64 288c-35.3 0-64 28.7-64 64v64c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V352c0-35.3-28.7-64-64-64H64zm280 72a24 24 0 1 1 0 48 24 24 0 1 1 0-48zm56 24a24 24 0 1 1 48 0 24 24 0 1 1 -48 0z" />
                        </svg>
                        <span> <?= $_SESSION["DEBUG"]["SERVER"]["REQUEST_METHOD"] . ' ' . $_SESSION["DEBUG"]["SERVER"]["HTTP_STATUS"] ?></span>
                    </a>
                    <a target="_blank" class="nav-item nav-link" href="/Debugbar/debug.vue.php?section=query">
                        <svg height="0.75em" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="fa" viewBox="0 0 448 512">
                            <path d="M448 80v48c0 44.2-100.3 80-224 80S0 172.2 0 128V80C0 35.8 100.3 0 224 0S448 35.8 448 80zM393.2 214.7c20.8-7.4 39.9-16.9 54.8-28.6V288c0 44.2-100.3 80-224 80S0 332.2 0 288V186.1c14.9 11.8 34 21.2 54.8 28.6C99.7 230.7 159.5 240 224 240s124.3-9.3 169.2-25.3zM0 346.1c14.9 11.8 34 21.2 54.8 28.6C99.7 390.7 159.5 400 224 400s124.3-9.3 169.2-25.3c20.8-7.4 39.9-16.9 54.8-28.6V432c0 44.2-100.3 80-224 80S0 476.2 0 432V346.1z" />
                        </svg>
                        <span><?= count($_SESSION["DEBUG"]["QUERY"]) - 3 ?></span>
                        &nbsp;
                        <span style="font-size:0.8rem;">
                            <svg height="0.75em" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="fas" viewBox="0 0 512 512">
                                <path d="M304 48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zm0 416a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM48 304a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm464-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM142.9 437A48 48 0 1 0 75 369.1 48 48 0 1 0 142.9 437zm0-294.2A48 48 0 1 0 75 75a48 48 0 1 0 67.9 67.9zM369.1 437A48 48 0 1 0 437 369.1 48 48 0 1 0 369.1 437z" />
                            </svg>
                            <?= DebugService::formatTime($_SESSION["DEBUG"]['QUERY']["TOTAL_TIME"]) ?>
                        </span>
                    </a>
                    <a target="_blank" class="nav-item nav-link disabled" style="<?= ($_SESSION["DEBUG"]["LOG"]["NB_ERROR"] > 0 ? 'color:goldenrod;' : '') ?>" href="/Debugbar/debug.vue.php?section=log">
                        <svg height="0.75em" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="fa" viewBox="0 0 512 512">
                            <path d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480H40c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24V296c0 13.3 10.7 24 24 24s24-10.7 24-24V184c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z" />
                        </svg>
                        <span> <?= $_SESSION["DEBUG"]["LOG"]["NB_ERROR"] ?></span>
                    </a>
                    <a target="_blank" class="nav-item nav-link" href="/Debugbar/debug.vue.php?section=server">
                        <svg height="0.75em" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="fa" viewBox="0 0 448 512">
                            <path d="M438.6 150.6c12.5-12.5 12.5-32.8 0-45.3l-96-96c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.7 96 32 96C14.3 96 0 110.3 0 128s14.3 32 32 32l306.7 0-41.4 41.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l96-96zm-333.3 352c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 416 416 416c17.7 0 32-14.3 32-32s-14.3-32-32-32l-306.7 0 41.4-41.4c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-96 96c-12.5 12.5-12.5 32.8 0 45.3l96 96z" />
                        </svg>
                        <span><?= $_SESSION["DEBUG"]["SSL"]["NB_QUERY"] ?></span>
                        &nbsp;
                        <span style="font-size:0.8rem;">
                            <svg height="0.75em" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="fas" viewBox="0 0 512 512">
                                <path d="M304 48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zm0 416a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM48 304a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm464-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM142.9 437A48 48 0 1 0 75 369.1 48 48 0 1 0 142.9 437zm0-294.2A48 48 0 1 0 75 75a48 48 0 1 0 67.9 67.9zM369.1 437A48 48 0 1 0 437 369.1 48 48 0 1 0 369.1 437z" />
                            </svg>
                            <?= DebugService::formatTime($_SESSION["DEBUG"]["SSL"]["TOTAL_TIME"]) ?>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex" style="text-align:right;color:white;margin-right: 50px;">
        Made with &hearts; By Tonio &copy; 2023
    </div>
</nav>

<button class="debug-button">
    <svg height="0.75em" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="fa-bug" viewBox="0 0 512 512">
        <path d="M256 0c53 0 96 43 96 96v3.6c0 15.7-12.7 28.4-28.4 28.4H188.4c-15.7 0-28.4-12.7-28.4-28.4V96c0-53 43-96 96-96zM41.4 105.4c12.5-12.5 32.8-12.5 45.3 0l64 64c.7 .7 1.3 1.4 1.9 2.1c14.2-7.3 30.4-11.4 47.5-11.4H312c17.1 0 33.2 4.1 47.5 11.4c.6-.7 1.2-1.4 1.9-2.1l64-64c12.5-12.5 32.8-12.5 45.3 0s12.5 32.8 0 45.3l-64 64c-.7 .7-1.4 1.3-2.1 1.9c6.2 12 10.1 25.3 11.1 39.5H480c17.7 0 32 14.3 32 32s-14.3 32-32 32H416c0 24.6-5.5 47.8-15.4 68.6c2.2 1.3 4.2 2.9 6 4.8l64 64c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0l-63.1-63.1c-24.5 21.8-55.8 36.2-90.3 39.6V240c0-8.8-7.2-16-16-16s-16 7.2-16 16V479.2c-34.5-3.4-65.8-17.8-90.3-39.6L86.6 502.6c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l64-64c1.9-1.9 3.9-3.4 6-4.8C101.5 367.8 96 344.6 96 320H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H96.3c1.1-14.1 5-27.5 11.1-39.5c-.7-.6-1.4-1.2-2.1-1.9l-64-64c-12.5-12.5-12.5-32.8 0-45.3z" />
    </svg>
</button>

<script>
    $(document).ready(function() {
        // Vérifiez si DEBUG_SHOW est stocké dans localStorage et initialisez-le si ce n'est pas le cas
        if (localStorage.getItem("DEBUG_SHOW") === null) {
            localStorage.setItem("DEBUG_SHOW", "true");
        }

        // Fonction pour afficher ou masquer le débogage
        function toggleDebug() {
            var debugElement = $("#debug");
            var debugShow = localStorage.getItem("DEBUG_SHOW") === "true";

            if (debugShow) {
                debugElement.show();
            } else {
                debugElement.hide();
            }
        }

        // Appel initial pour définir l'état du débogage
        toggleDebug();

        // Gestionnaire de clic pour le bouton de débogage
        $(".debug-button").click(function() {
            var currentDebugShow = localStorage.getItem("DEBUG_SHOW") === "true";
            var newDebugShow = !currentDebugShow;
            localStorage.setItem("DEBUG_SHOW", newDebugShow.toString());
            toggleDebug();
        });
    });
</script>
