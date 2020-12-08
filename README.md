Backendová část aplikace založená na PHP

1) Naklonujte repozitář
2) Vytvořte MySQL sever 'ares' a importujte tabulky pomocí ares.sql
3) Nastavte připojení k DB v konstruktoru, v souboru /app/conf/Env.php
4) Spusťte server, webroot musí být /www (např. '"C:\Program Files\Ampps\php-7.3\php.exe" "-S" "localhost:8000" "-t" "www"')
