<header>
    <div class="header">
        <div class="wrapper">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class=" my-0 text-center cursive "><a href="index.php" title="Home">Global<span class="secondary">List</span> <i class="fa-regular fa-clipboard"></i></a></h1>
                <div class="sub-menu">
                    <input type="checkbox" id="menu-toggle">
                    <label for="menu-toggle" class="menu-icon">&#9776;</label>
                    <ul class="nav-list d-flex align-items-center justify-content-center">
                        <?php foreach ($vetMenu as $key => $valor) : ?>
                            <li><a href="<?= $valor['url'] ?>" title="<?= $valor['title'] ?>"> <?= $valor['icon'] ? '<i class="' . $valor['icon'] . '"></i>' : '' ?> <?= $valor['title'] ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                        </div>
            </div>
    </div>
</header>
<div class="header--block"></div>