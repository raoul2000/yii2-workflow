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
