# 1v1 Predict

####LIVE DEMO AT http://pebocomputers.com/Riot/

Welcome to my entry for the April 2016 Riot API challenge.


This project aims to make an accurate and reliable prediction of who would win a 1v1.


This prediction uses data on:

* K/D/A
* CS
* Winrate
* Rank

...but specifically uses the data for your most mastered champion.


Your rank will also be included in the prediction.
Ranked tier is compared, and if the tiers are the same (Silver = Silver) then the divisions are compared (Silver II =/= Silver V).


### The 1v1 Predictor creates a weighted comparison of two summoners.

This weighted comparison allows us to determine the winner of a 1v1.


The comparison is weighted so advantages in categories are worth different amounts.

In order of greatest weight to least weight:


1. Ranked Tier

2. K/D/A ratio

3. Creep Score

4. Winrate

5. Ranked Division


More specifically, it's weighted so that you can get a certain number of points in each category that is compared. Each category has a max number of points that can be attained, and is weighted that way, too.


The max number of points attainable for each category is below:

| Category | Points |
| -------------- | --------- |
| Ranked Tier | 310 |
| K/D/A | 127 |
| Creep Score | 102 |
| Winrate | 76 |
| Division (if applicable) | 35 |


Now, let's talk about some of the technical stuff.

#### How do we fetch data about summoners?

Using PHP, we query the Riot API, using the first summoner provided. We check for empty information occasionally. (In matchup.php)

* First off, we use `session_start();` to store error data. More on this later.

* Next, All functions to retrieve data from Riot are defined.

* We retrieve Summoner ID, checking for emptiness.

* Next, we check what __champion__ they have mastered. 1v1 Predict grabs their most mastered champion.

* After that, we check their ranked __winrate__ with that champion, and convert it to a percentage.

* Next, we grab __K/D/A__ for that champion, and round it to the nearest hundredth.

* __Creep Score__ is calculated next, and rounded to the nearest whole number.

* After that, __Ranked Tier__ and __Division__ are grabbed.

#### Error data with `session_start();`? Emptiness checks? How do those work?

PHP's `session` allows for storage of data across pages. If a crucial element of the comparison is detected to be empty with `empty()`, session stores the error message in the browser, temporarily (matchup.php). The user is then redirected to an error page, where the error is read from the browser storage and then written on the page (error.php).

#### How are you comparing Ranked Divisions and Tiers? Those aren't numbers!

Ranked divisions and tiers are NOT numbers. In order to compare them easily, an `enum` should be created. Unfortunately, PHP has no native enum functionality, so it was replicated through `abstract class` constants (matchup.php). We assign divisions and tiers numbers so we can compare them easily. Tiers make sense, but divisions are counter-intuitive, since a Roman Numeral 5 is actually a zero here, while a Roman Numeral 1 is a 5.

* Bronze = 0
* Silver = 1
* Gold = 2
* ...etc...

* V = 0
* IV = 1
* III = 2
* ...etc...


Then we can compare: `constant('Tiers::Bronze') < constant('Tiers::Gold')` and it will return `True`.

Likewise, we can compare: `constant('Divisions::V') < constant('Divisions::III')` and it will return `True`.

#### Setup/Usage

Setting this up on your own is very easy. After installing PHP, clone this repository. Place the repo files in a place that PHP can serve them. You'll also need to replace `<API_KEY_HERE>` with your actual API key from https://developer.riotgames.com in matchup.php. Everything else should work out of the box. All dependencies are included.
