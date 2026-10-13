<?php

namespace modules\template\views;
class Layout{
    public function __construct(  
        private string $titre,
        private string $description ,
        private string $contenu
    ){}
      
    public function show(): void{
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8"/>
    <meta name="description" content="<?php echo htmlspecialchars($this->description) ?>"/>
    <title><?php echo htmlspecialchars($this->titre) ?></title>
</head>
<body>
<header>
    <!-- HEADER -->
</header>
<main>
    <?php echo '<h1>' . $this->titre . '</h1>' ?>
    <?php echo $this->contenu; ?>
</main>
<footer class="footer">
    <!-- FOOTER -->
</footer>
</body>
</html>
    <?php
    }
}