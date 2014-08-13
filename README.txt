
This extension is copyrighted eZ Systems 2008
All rights reserved


This extension makes it possible to access the $GLOBALS['eZCurrentAccess'] variable
from templates.

This array typically contains at least two elements:
name : Name of the siteaccess, for instance 'eng', 'nor' or 'ezwebin_site'
type : An integer identifying how the siteaccess was matched (by uri or host name for instance)
       See constants defined in access.php for possible values of this variable

The operator have two usages:
{siteaccess()} : which will return the whole $GLOBALS['eZCurrentAccess'] array

{siteaccess('[key]')} : will return the element in $GLOBALS['eZCurrentAccess'] specified by [key]

Examples : 
{siteaccess('type')}
{siteaccess('name')}
{siteaccess()}


Combining "uri" and "host" in MatchOrder
========================================

The use of $GLOBALS['eZCurrentAccess'] variable in templates can have several objectives. However,
this chapter will explain one scenario where this is particular useful ( and the direct problem
that caused this extension to be written in the first place ).

It is possible to combine both "uri" and "host" in MatchOrder in site.ini, example:
[SiteAccessSettings]
MatchOrder=host;uri

You will then probably get into trouble if you create {cache-blocks} where URI is used as cache key.

Consider the following additional configuration:

[SiteAccessSettings]
AvailableSiteAccessList[]
AvailableSiteAccessList[]=eng
AvailableSiteAccessList[]=nor
AvailableSiteAccessList[]=ger
HostMatchMapItems[]
HostMatchMapItems[]=mysite.co.uk;eng

[SiteSettings]
DefaultAccess=eng


Accessing mysite.co.uk will use the "eng" siteaccess because of the host matching
Accessing for instance mysite.net will not match any host matching rule and the default ("eng") siteaccess will be used
Accessing mysite.net/ger will use the "ger" siteaccess due the uri matching.
Accessing mysite.net/eng will use the "eng" siteaccess, also due the uri matching.

Now, let's say you have a cache block in your pagelayout where you generate a left menu:
{cache-block expiry=86400 keys=array( $module_result.uri )}
{* ... some template code for left menu ++++... *}
{/cache-block}

Then access some page using uri matching (first clear all template-block cache), for instance mysite.net/eng/
You should then notice that links in your left menu also includes the "eng/" element in the beginning of the URIs (which is correct behavior). Let's say you have a link to the News folder. It should look like this : mysite.net/eng/News

Then access the same siteaccess using host matching:
mysite.co.uk/

Now, how do you think the link to your News folder will look like ? It will link to mysite.co.uk/eng/News instead of mysite.co.uk/News !!!!!
This is because template blocks are cached per site access and you access the same siteaccess with two different matching types ( and this have influence on how the URLs should look like ).

The solution to this problem is to use the ezsiteaccess operator and include it in the cache-block key:
{cache-block expiry=86400 keys=array( $module_result.uri, siteaccess('type') )}
{* ... some template code for left menu ++++... *}
{/cache-block}

Then clear template-block cache and everything should work as expected.
