Betaout_Analytics
============

*Betaout_Analytics* is a [Betaout] web analytics module for the
[Magento 2][magento] eCommerce platform.  betaout is an extensible
free/libre analytics tool that can be self-hosted, giving you complete
data ownership.  Betaout_Analytics lets you integrate Betaout with your
Magento 2 store front.


Installation
------------

To install Betaout_Analytics, download and extract the
[master zip archive][download] and move the extracted folder to
*app/code/Betaout/Analytics* in your Magento 2 installation directory.

```sh
unzip magento2-betaout-analytics-master.zip
mkdir app/code/Betaout
mv magento2-betaout-analytics-master app/code/Betaout/Analytics
```

Alternatively, you can clone the Betaout_Analytics Git repository into
*app/code/Betaout/Analytics*.

```sh
git clone https://github.com/betaout/magento2-betaout-analytics.git app/code/Betaout/Analytics
```

Or, if you prefer, install it using [Composer][composer].

```sh
composer config repositories.betaoutanalytics git https://github.com/betaout/magento2-betaout-analytics.git
composer require betaout/module-analytics:dev-master
```

Finally, enable the module with the Magento CLI tool.

```sh
php bin/magento module:enable Betaout_Analytics --clear-static-content
```


Configuration
-------------

Once intsalled, configuration options can be found in the Magento 2
administration panel under *Stores/Configuration/Sales/Betaout API*.
To start tracking, set *Enable Tracking* to *Yes*, enter the
*Project ID* AND *Api KEY*configured in Magento is correct.

Disclaimer
----------

Henhed_Piwik is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the [GNU
Affero General Public License][agpl] for more details.

[agpl]: http://www.gnu.org/licenses/agpl.html
    "GNU Affero General Public License"
[composer]: https://getcomposer.org/
    "Dependency Manager for PHP"
[magento]: https://magento.com/
    "eCommerce Software & eCommerce Platform Solutions"
[betaout]: http://app.betaout.com/
    "Free Web Analytics Software"
