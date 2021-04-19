[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)
![100/100 on Lighthouse](https://user-images.githubusercontent.com/793063/114666826-65039e80-9d07-11eb-9d67-b66da4686787.png)

# Flipsite

> Flipsite is a static compiler and middleware for serving lighting fast websites

[Flipsite](https://flipsite.io) is **NOT** your typical CMS or static website compiler – but it happens to work as that too. It lets you build your websites fast, without writing any HTML yourself, by concentrating on the structure of your site. It then automatically serves beautiful DOM structures and optimized images with lazy loading and that are responsive and screen reader friendly.

## Requirements

- [PHP8](https://www.php.net/)
- [Composer](https://getcomposer.org/)

## Getting Started

Create a new site with [Composer](https://getcomposer.org/) and define [flipsite/flipsite](https://packagist.org/packages/flipsite/flipsite) as a dependency. You can do it during the init or when it's complete using `composer require flipsite/flipsite`

```
composer init
composer require flipsite/flipsite
```

Then run the create example site command using the command line interface.
```
./vendor/bin/flipsite site:example
```

Start the server. The site will usually run at [127.0.0.1:8001](http://127.0.0.1:8001). Otherwise check the console log for the local IP.
```
./vendor/bin/flipsite server:run
```

## Documentation

We work daily on making our [documentation](https://docs.flipsite.io/) even better. If something is unclear or missing, please open an issue in the [documentation repo](https://github.com/docs.flipsite.io).

## Contributing

Thank you for considering contributing to Flipsite! Contact us, submit an issue or go ahead and make a pull request.

## Important Links

- [Flipsite Website](https://flipsite.io)
- [Flipsite Documentation](https://docs.flipsite.io/)

Example websites built with Flipsite:
- [https://emathstudio.com](https://emathstudio.com)
- [https://wecke.fi](https://wecke.fi)
- [https://knegarn.ax](https://knegarn.ax)
- [https://getabetong.ax](https://getabetong.ax)
- [https://hogmansmaleri.ax/](https://hogmansmaleri.ax/)

## License

[Apache 2.0](LICENSE)
