<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Paramètres de l’entreprise

Les administrateurs peuvent ouvrir `/settings` depuis le menu latéral ou le menu utilisateur. La page permet de modifier les coordonnées de l’entreprise, la devise, la TVA, le fuseau horaire, le format de date, les notifications par email, le logo et la couleur principale. Les paramètres sont partagés dans les vues et mis en cache pendant une heure.

Les ventes enregistrent le taux et le montant de TVA appliqués au moment de leur création, afin que les tickets conservent leur calcul historique. Les logos acceptés sont JPG, PNG, GIF et WEBP (2 Mo maximum) ; pour les rendre accessibles publiquement, exécutez `php artisan storage:link`.

Le catalogue permet de préciser la concentration d’un parfum (EDT, EDP, extrait, Cologne ou brume). Les réceptions et entrées manuelles peuvent identifier chaque lot et sa date d’expiration ; les ventes consomment en priorité les lots non expirés les plus proches de leur date limite. Le suivi est accessible depuis **Stock → Suivi des lots**. Les quantités déjà en stock sont reprises dans des lots d’ouverture lors de la migration.

Le catalogue affiche une marge indicative au prix actuel (prix promotionnel compris). Les bénéfices des ventes restent calculés et conservés au moment de chaque transaction.

Le réglage des notifications mémorise la préférence de l’entreprise. L’envoi automatique dépend des événements de notification disponibles ; les fonctionnalités de facturation et d’envoi de facture par email ne font pas partie du module de ventes actuel.

## Fonctionnement hors ligne

Les styles et scripts de l’interface (Tailwind CSS et Alpine.js) sont compilés et servis depuis le projet. Après l’installation des dépendances npm, exécutez `npm run build` avant le déploiement ; le navigateur n’a ensuite besoin d’aucun CDN ni accès Internet pour afficher l’application. Les images de remplacement du catalogue sont également générées localement.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
