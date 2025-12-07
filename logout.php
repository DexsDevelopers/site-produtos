<?php
// logout.php

session_start();

// Remove todas as variáveis da sessão
session_unset();

// Destrói a sessão
session_destroy();

// Redireciona para a página de login com uma mensagem
// (A mensagem não será na sessão, pois ela foi destruída)
header("Location: login.php");
exit();
?>