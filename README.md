<a id="readme-top"></a>

![Contributors](https://img.shields.io/github/contributors/kerkenit/classes)
![Forks](https://img.shields.io/github/forks/kerkenit/classes)
![Repo stars](https://img.shields.io/github/stars/kerkenit/classes)
![Issues](https://img.shields.io/github/issues/kerkenit/classes)
![License](https://img.shields.io/github/license/kerkenit/classes)
![Version](https://img.shields.io/github/package-json/v/kerkenit/classes)


<!-- PROJECT LOGO -->
<br />
<div align="center">
  <a href="https://github.com/kerkenit/classes">
    <img src="images/logo.svg" alt="Logo" width="300">
  </a>

<h3 align="center">Classes</h3>

  <p align="center">
    All common classes developed by Kerk en IT
    <br />
    <!--<br />
    <a href="https://github.com/kerkenit/classes">View Demo</a>
    &middot;-->
    <a href="https://github.com/kerkenit/classes/issues/new?labels=bug&template=bug-report---.md">Report Bug</a>
    &middot;
    <a href="https://github.com/kerkenit/classes/issues/new?labels=enhancement&template=feature-request---.md">Request Feature</a>
  </p>
</div>



<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
         <li><a href="#prerequisites">Prerequisites</a></li>
         <li><a href="#php-version-requirements">PHP Version Requirements</a></li>
         <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#roadmap">Roadmap</a></li>
    <li><a href="#contributing">Contributing</a></li>
    <li><a href="#license">License</a></li>
    <li><a href="#contact">Contact</a></li>
  </ol>
</details>



<!-- ABOUT THE PROJECT -->
## About The Project

In this project you will find all classes crafted by Marco van 't Klooster, owner of Kerk en IT. These classes have been developed since 2010 and are a masterpiece. You can use them in your project or assist me in enhancing these classes by improvements or detecting security or memory issues.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- GETTING STARTED -->
## Getting Started

This is an example of how you may give instructions on setting up your project locally.
To get a local copy up and running follow these simple example steps.

### Prerequisites

You need to be familiar to the commandline in in UNIX environment

### PHP Version Requirements

> **Best / recommended version: PHP 8.4 or higher.**
> **Global minimum for the whole project: PHP 8.4** — because `class.Video.php` uses
> PHP 8.4 *typed property accessors* (`public bool $x { get / set }`).
> Most classes run on **PHP 8.0+**; only `class.Video.php` truly requires 8.4.

Each class file documents its own minimum in the header docblock
(`PHP versions X or higher …`). The autoloader in `src/autoload.php` enforces this:
on **PHP 8.4+** every file is loaded; on **PHP 8.3** any file whose docblock contains
`PHP versions 8.4` is skipped (only `class.Video.php` matches); on **PHP < 8.3** all
files are loaded unconditionally.

| Class | Min. PHP | Reason (newest feature used) | Key extension / dependency |
|-------|:--------:|------------------------------|----------------------------|
| `class.ColorPalette` | **7.0** | no 8.x-only syntax | ext-imagick, ext-json |
| `class.Math` | **7.0** | no 8.x-only syntax | — |
| `class.Memcache` (dummy `\Memcache`) | **7.4** | typed static properties | ext-memcache (optional fallback) |
| `class.Cache` | **8.0** | `mixed` + `?int` type hints | global `\Memcache` |
| `class.Meta` | **8.0** | union types (`string\|false`) | ext-curl, ext-dom, ext-gd, ext-filter |
| `class.Console` | **8.0** | `mixed`, `float\|null` property | ext-intl (optional) |
| `class.DateTime` | **8.0** | `mixed`, union types, `??` | ext-intl |
| `class.ErrorHandeling` | **8.0** | union types, `str_contains` | ext-mysqli (optional) |
| `class.Format` | **8.0** | `mixed`, `str_starts_with` | ext-intl, ext-mbstring, ext-iconv |
| `class.GeoLocation` | **8.0** | `mixed` in closures, `str_contains` | ext-curl |
| `class.KerkEnIT` | **8.0** | union types, `str_starts_with` | ext-filter; extends `Networking` |
| `class.Log` | **8.0** | `mixed` type hints, `str_contains` | ext-mysqli (optional) |
| `class.Mailer` | **8.0** | `string\|bool`, `str_contains` | PHPMailer (composer), ext-openssl |
| `class.Networking` | **8.0** | union types `string\|bool` | ext-filter |
| `class.SQL` | **8.0** | union type `array\|object` | ext-mysqli |
| `class.Image` | **8.1** | backed enum `ImageMimeTypes` | ext-gd |
| `class.Convert2` | **8.3** | `#[\Deprecated]` attribute | ext-imagick, ext-gd |
| `class.Cryptography` | **8.3** | `#[\Deprecated]` attribute | ext-openssl |
| `class.Video` | **8.4** | typed property accessors | ffmpeg / ffprobe binaries |

**Recommended extensions (install all to use every class):**
`ext-curl`, `ext-dom`, `ext-gd`, `ext-imagick`, `ext-intl`, `ext-mbstring`,
`ext-iconv`, `ext-filter`, `ext-mysqli`, `ext-openssl`, `ext-json`, and
the `phpmailer/phpmailer` Composer package (for `class.Mailer`).
`ext-memcache` is optional — `class.Memcache.php` provides a no-op dummy fallback.

### Installation

1. Open the terminal
2. Navigate to your project
   ```sh
   cd ~/Sites/DEV/my.amazing.project
   destination=$(pwd)
   ```
3. Link the classes you want
   ```sh
	src=$(pwd)
	read -r -a classes <<< $(ls $src/src/*.php | xargs -n 1 basename | sed 's/\.php//g')
	for((i=0;i<${#classes[@]};i++))
	do
		class="${classes[$i]}"
		input="$src/src/$class.php"
		file="$destination/includes/classes/KerkEnI/$class.php"
		echo "Linking $input to $file"
		ln "$input" "$file"
	done
   ```
4. Repeat for step 3 for every class you need.

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- ROADMAP -->
## Roadmap

- [x] Import all other projects and merge the into one big class
- [ ] Complete all classes, functions and properties with correct documentation

See the [open issues](https://github.com/kerkenit/classes/issues) for a full list of proposed features (and known issues).

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- CONTRIBUTING -->
## Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

<p align="right">(<a href="#readme-top">back to top</a>)</p>

### Top contributors:

<a href="https://github.com/kerkenit/classes/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=kerkenit/classes" alt="contrib.rocks image" />
</a>



<!-- LICENSE -->
## License

Distributed under the GNU GPLv3. See `LICENSE.txt` for more information.

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- CONTACT -->
## Contact

Marco van 't Klooster - [@kerkenit](https://x.com/kerkenit) - info@kerkenit.nl

Project Link: [https://github.com/kerkenit/classes](https://github.com/kerkenit/classes)

<p align="right">(<a href="#readme-top">back to top</a>)</p>