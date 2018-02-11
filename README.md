# Neos Listable !! WIP !!
[![Latest Stable Version](https://poser.pugx.org/breadlesscode/neos-listable/v/stable)](https://packagist.org/packages/breadlesscode/neos-listable)
[![Downloads](https://img.shields.io/packagist/dt/breadlesscode/neos-listable.svg)](https://packagist.org/packages/breadlesscode/neos-listable)
[![License](https://img.shields.io/github/license/breadlesscode/neos-listable.svg)](LICENSE)
[![GitHub stars](https://img.shields.io/github/stars/breadlesscode/neos-listable.svg?style=social&label=Stars)](https://github.com/breadlesscode/neos-listable/stargazers)
[![GitHub watchers](https://img.shields.io/github/watchers/breadlesscode/neos-listable.svg?style=social&label=Watch)](https://github.com/breadlesscode/neos-listable/subscription)

This Neos CMS plugin is for listing and paginate NodeTypes in Fusion.
This package is heavily inspired by [Flowpack.Listable](https://github.com/Flowpack/Flowpack.Listable), thanks for that :)

## Installation
Most of the time you have to make small adjustments to a package (e.g., the configuration in Settings.yaml). Because of that, it is important to add the corresponding package to the composer from your theme package. Mostly this is the site package located under Packages/Sites/. To install it correctly go to your theme package (e.g.Packages/Sites/Foo.Bar) and run following command:

```bash
composer require breadlesscode/neos-listable --no-update
```

The --no-update command prevent the automatic update of the dependencies. After the package was added to your theme composer.json, go back to the root of the Neos installation and run composer update. Your desired package is now installed correctly.

## Example
```
prototype(Vendor.Xy:MyPersonalList) < prototype(Breadlesscode.Listable:List) {
    collection = ${ q(site).find('[instanceof Vendor.Xy:BlogPost]') }
    itemsPerPage = ${ 10 }
    itemRenderer = 'Vendor.Xy:MyPersonalListItem'
    itemName = ${ 'item' }
    # if you want no pagination you can set this property:
    # paginated = ${ false }
}

prototype(Vendor.Xy:MyPersonalListItem)  < prototype(Neos.Fusion:Tag) {
    tagName = 'a'
    content = ${ q(item).property('title') }
    attributes {
        href = Neos.Neos:NodeUri {
            node = ${ item }
        }
    }

    @process.headlineWrap = ${ '<h2>' + value '</h2>' }
}

```
## Configuration
You have to possibilities to configure the pagination of this package. You can set the configurations global via `Settings.yaml`:

```yaml
Breadlesscode:
  Listable:
    pagination:
      showSeperators: true
      showNextAndPrevious: true
      showFirstAndLast: true
      labels:
        seperator: '&hellip;'
        previous: '&lang;'
        next: '&rang;'
        first: '&lang;'
        last: '&raquo;'
```

And you can overwrite this configuration in Fusion for a specific list:

```
prototype(Vendor.Xy:MyPersonalList) < prototype(Breadlesscode.Listable:List) {
    # ...
    paginationConfig {
        showSeperators = ${ true }
        showNextAndPrevious = ${ true }
        showFirstAndLast = ${ true }

        labels {
            seperator = ${ '&hellip' }
            previous = ${ '&lang;' }
            next = ${ '&rang;' }
            first = ${ '&lang;' }
            last = ${ '&rang;' }
        }
    }
}
```


## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
