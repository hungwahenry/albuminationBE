MusicBrainz API
The API discussed here is an interface to the MusicBrainz Database. It is aimed at developers of media players, CD rippers, taggers, and other applications requiring music metadata. The API's architecture follows the REST design principles. Interaction with the API is done using HTTP and all content is served in a simple but flexible format, in either XML or JSON. XML is the default format; to get a JSON response, you can either set the Accept header to "application/json" or add fmt=json to the query string (if both are set, fmt= takes precedence).
General FAQ
What can I do with the MusicBrainz API?
You can look up information about a particular MusicBrainz entity ("give me info about The Beatles"), browse the data to find entities connected to a particular entity ("show me all releases by The Beatles"), or search for entities matching a specific query ("show me all artists matching the query 'Beatles' so I can find the one I want and ask for more data").
Who can use the MusicBrainz API? Is it free?
Non-commercial use of this web service is free; please see our commercial plans or contact us if you would like to use this service commercially.
Do I need an API key?
Currently, no. But you must have a meaningful user-agent string.
Do I need to provide authentication?
See MusicBrainz_API#Authentication.
Which formats can I get the data in?
The API was originally written to return XML, but nowadays it can also return JSON.
Is there any significant difference between the XML and JSON APIs?
For requesting data, the XML and JSON API are effectively equivalent. The XML API is the only one that allows submitting data to MusicBrainz (but keep in mind only ratings, tags, barcodes and ISRCs can be submitted via the API at all; for most data additions you should use the website instead).
Is there a limit to the number of requests I can make per second?
Yes. See our rate limiting rules.
This seems very complicated, can I see some examples?
Yes, we have an example page showcasing some queries and showing the returned format you can expect for each.
Are there language bindings for the API?
Yes, in many different languages. See our list of external libraries.
What should I do if I encounter unexpected behaviour not covered in these docs?
You can ask question in IRC or in the forums.
Check to see if a ticket has been filed in the bug tracker, and if not consider writing one.
What else should I know before I start using the API?
It'd probably be helpful to know:
How MusicBrainz is structured
What relationships are available
So you're on version 2 of the API then? What happened to version 1?
The version 1 of the API was designed with the data structure of the original (pre-2011) version of the MusicBrainz database in mind. It was deprecated in 2011 when we changed to our current data schema, and after running (without further updates) for several years to avoid breaking any tools using it, it was finally taken down in 2019.
Do you ever make breaking changes?
We try to avoid that, but sometimes we might need to do so. In those cases, they will be announced on our blog, so consider following that!
Application rate limiting and identification
All users of the API must ensure that each of their client applications never make more than ONE call per second. Making more than one call per second drives up the load on the servers and prevents others from using the MusicBrainz API. If you impact the server by making more than one call per second, your IP address may be blocked preventing all further access to MusicBrainz. Also, it is important that your application sets a proper User-Agent string in its HTTP request headers. For more details on both of these requirements, please see our rate limiting page.

Authentication
Data submission, as well as requests that involve user information, require authentication. You can authenticate using OAuth, or digest authentication over HTTPS; for digest authentication, use the same username and password used to access the main https://musicbrainz.org website.

Introduction
The API root URL is https://musicbrainz.org/ws/2/.

We have 13 resources on our API which represent core entities in our database:

 area, artist, event, genre, instrument, label, place, recording, release, release-group, series, work, url
We also provide an API interface for the following non-core resources:

 rating, tag, collection
And we allow you to perform lookups based on other unique identifiers with these resources:

 discid, isrc, iswc
On each entity resource, you can perform three different GET requests:

 lookup:   /<ENTITY_TYPE>/<MBID>?inc=<INC>
 browse:   /<RESULT_ENTITY_TYPE>?<BROWSING_ENTITY_TYPE>=<MBID>&limit=<LIMIT>&offset=<OFFSET>&inc=<INC>
 search:   /<ENTITY_TYPE>?query=<QUERY>&limit=<LIMIT>&offset=<OFFSET>
... except that browse and search are not implemented for genre entities at this time.

Note Note: Keep in mind only the search request is available without an MBID (or, in specific cases, a disc ID, ISRC or ISWC). If all you have is the name of an artist or album, for example, you'll need to make a search and pick the right result to get its MBID; only then will you able to use it in a lookup or browse request.

On the genre resource, we support an "all" sub-resource to fetch all genres, paginated, in alphabetical order:

 all:      /genre/all?limit=<LIMIT>&offset=<OFFSET>
The /genre/all resource, in addition to supporting XML and JSON, can output all genre names as text by specifying fmt=txt or setting the Accept header to "text/plain". The genre names are returned in alphabetical order and separated by newlines. (limit and offset are not supported for the txt format.)

Of these first three types of requests:

Lookups, non-MBID lookups and browse requests are documented in following sections, and you can find examples on the dedicated examples page.
Searches are more complex and are documented on the search documentation page.
