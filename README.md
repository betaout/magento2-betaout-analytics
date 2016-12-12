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

Description
-----------
#Betaout, All-­in-­One eCommerce Marketing Software

Your customer is engaging with you via multiple channels including your website, mobile, app, email, sms, social networks etc, and to facilitate communication with your customer across different channels, you are using a standalone software. So, instead of getting your data trapped in 3-4 different silos, Beatout offers all-in-one marketing automation software with a centralized database. It combines customer segmentation, email marketing, on-site engagement, and marketing analytics together as one. Our powerful marketing solution helps you to harness and leverage your customer data collected across multiple channels and touchpoints to boost conversion and retention.
* **The complete Email marketing solution**

With the email marketing feature you can set up data driven email campaigns, including welcome emails, activation emails, win back and retention emails.You can create and send beautiful newsletters with WYSIWYG editor. One can set-up lifecycle emails, behavioral emails and advanced triggered campaigns with zero technical knowledge. This helps in list management such as Unsubscribes, Spam and Bounce.The powerful analytics goes beyond opens and clicks which helps the user to have a real-time detailed report.This gives a complete view of every campaign you have send with actionable stats. It also optimizes deliverability with send at best time feature.

* **Customer Segmentation**

In this feature you can segment your customer base based on real-time data. The advanced segmentation is based on all attributes, demographics, customer lifecycle, customer behavior, and purchase activity.You can create refined segments on the fly, no coding is required.

The segments are based on RFM metrics (Recency, Frequency, Monetary Value) which gives a detailed & real-time segment insight by which you get automatic segment growth reports. This also helps in exporting segmented lists into CSV format.

* **360 Customer Profiles**

An insight to a single view of customer profile with demographic details, events & activity details across multiple touch point.

* **Cart Abandonment**

Cart Abandonment feature helps to trigger multi-step abandoned cart reminders. You can personalize the message based on the targeted segment by inserting dynamic content and adding product information. This feature gives a detailed analytics on recovered revenue.

* **On­-site engagement**

This feature proactively initiates on-page chat with your visitors. One can have a private inbox for every team member. It helps you to create pullup tab, sticky bar and other notifications in a visual editor. Using this feature you can set-up advanced trigger rules to show light box. You can set-up real-time notifications when customer is on your website.
Marketing templates and recipes

This feature gives an extensive resource base with email inspirations. The email gallery is filled with ready-to-use HTML templates. It has pre-built segmentation ideas. It also helps you to target a user to a landing page.
Additional features -

The software helps to auto-calculate RFM score (Recency, Frequency, Monetary value).You can import historical data & full order history. A detailed analytics is available for order data. Reports are automatically generated on orders with or without promocode.

* **Pricing**

The services of this software is free for upto 150,000 emails per month and 10,000 emails to addressable contacts. For more details do checkout Betaout’s pricing page.
View Demo/Book a Live Demo

Checkout a working demo at www.betaout.com, or schedule a live demo by emailing us at contact@betaout.com. Sign up today for your 14-day free trial.
Documentation

Installation Manual Link https://www.betaout.com/ideas/product-guide


Disclaimer
----------

Betaout_Analytics is distributed in the hope that it will be useful, but
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
