# User Guide

## Prerequisite

The User Guide is designed to be built by **[mkDocs](http://www.mkdocs.org/)** based on a [Bootswatch theme](https://github.com/mkdocs/mkdocs-bootswatch)

```
pip install mkdocs
pip install mkdocs-bootswatch
```

More info about **mkDocs** installation [here](http://www.mkdocs.org/#installation)

## Building the Guide

During dev, the guide can be served from a local server in charge of refreshing the page on each
change. To start the local server, enter :

```
cd guide
mkdocs serve
```

When the guide is ready to be published, build is with :

```
mkdocs build
```

The user guide is built into the folder `guide\site`.

# Class Reference

## Prerequisite

The class reference documentation is built using [apiGen](http://www.apigen.org/) using the *bootstrap* built-in theme.

To install *apiGen*, [download the apigen.phar](http://apigen.org/apigen.phar) file into the project folder.

## Building The Class Reference Doc

From the project's main folder :

```
php apigen.phar generate -s src -d guide\api --template-theme bootstrap --no-source-code --title "yii2-workflow Class Reference"
```

The documentation is built into the folder `guide\api`.
