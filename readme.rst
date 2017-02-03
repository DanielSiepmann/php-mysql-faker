.. _highlight: bash

About
=====

Small wrapper around `faker`_ and `Doctrine DBAL`_ to seed MySQL Tables with dummy data.

Because no other found solution did work out of the box.

Installation
============

Run::

    composer update

Usage
=====

Provide a configuration somewhere, containing necessary database connection information and data
seed information.

Call ``fake:mysql`` with configuration file and table name::

    ./app fake:mysql configs/typo3_downloads.yml tx_downloadcounter_domain_model_download

An example file is provided.

Userfunctions
=============

You can define custom PHP Code to provide information for each column.

Configuration
=============

The configuration has two sections, ``database`` and ``data``.

``database`` is just a plain key-value array passed to doctrines dbal. So take a look at
http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html for
further information.

``data`` is a key-value pair of column names and information which data to use. The structure is
like the following:

.. code:: yml

  pid:
    type: static

Where ``pid`` is the column name and ``type`` defines how to get the data to use. The following
types are supported:

``static``
    Uses the value of ``value`` as static value.
    E.g.:

    .. code:: yml

        pid:
            type: static
            value: 1

``userfunc``
    Uses a user defined function defined by ``class`` and ``method``.

    .. code:: yml

        name:
            type: userfunc
            class: DSiepmann\Userfunction\RepeatingFilenames
            method: filename


.. _faker: https://github.com/fzaninotto/Faker
.. _Doctrine DBAL: http://www.doctrine-project.org/projects/dbal.html
