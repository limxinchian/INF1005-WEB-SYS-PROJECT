<nav class="navbar navbar-expand-lg navbar-light border-bottom border-primary border-5">
    <div class="container-fluid d-flex align-items-center gap-3">
        <div>
            <a href="index.php" class="navbar-brand ps-3">
                <img src="assets/images/logo.png" alt="Logo" width="65">
            </a>
        </div>

        <div class="flex-grow-1 d-none d-lg-block">
            <form action="" method="get">
                <input type="text" name="search" placeholder="Butter Chicken Curry" class="form-control bg-white">
            </form>
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <div class="d-lg-none my-3">
                <form action="" method="get">
                    <input type="text" name="search" placeholder="Butter Chicken Curry" class="form-control bg-white">
                </form>
            </div>
            <div class="d-flex gap-3 ms-lg-auto flex-row flex-lg-row justify-content-end">
                <button class="btn btn-secondary w-nav_button"><span class="overpass-mono-light text-white fw-bold">Login</span></button>
                <button class="btn btn-secondary w-nav_button"><span class="overpass-mono-light text-white fw-bold">Register</span></button>
            </div>
        </div>
    </div>
</nav>