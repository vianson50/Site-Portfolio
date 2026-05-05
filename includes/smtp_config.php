<?php
/**
 * BLACK_PROTOCOL — SMTP Configuration (Gmail)
 *
 * ⚠️ IMPORTANT : Pour utiliser Gmail SMTP, vous devez :
 * 1. Activer la validation en 2 étapes sur votre compte Google
 * 2. Créer un "Mot de passe d'application" sur https://myaccount.google.com/apppasswords
 * 3. Coller ce mot de passe (16 caractères) dans SMTP_PASS ci-dessous
 */

// ── Gmail SMTP Settings ──
define("SMTP_HOST", "smtp.gmail.com");
define("SMTP_PORT", 465);
define("SMTP_USER", "vianson50@gmail.com"); // Votre adresse Gmail
define("SMTP_PASS", "zebbqqjoltbkgoay");
define("SMTP_FROM_NAME", "BLACK_PROTOCOL");
define("SMTP_FROM_EMAIL", "vianson50@gmail.com");

// ── Newsletter Settings ──
define("NL_EMAILS_PER_BATCH", 50); // Emails par lot (Gmail limite ~500/jour)
define("NL_PAUSE_BETWEEN", 2); // Secondes entre chaque email
