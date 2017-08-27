# The Pagificator
This is very simple, but yet powerful templating engine written in PHP, its closest conceptual relative seem to be {dwoo}.
The principle is simple: get your backend arrange the data you need to show in arrays of certain structure, put HTML templates with placeholders for your data for each type of items you need - and the Pagificator will handle the rest.

**Key features:**
* Taking templates and filling them with data produced by your backend logic
* Nesting HTML elements recursively according to data - no limit of maximum nested element
* Creating custom elements, which don't exist in the initial template at all
* Nesting custom elements inside other custom elements (useful for creating select boxes, for example). No limitations on depth of nesting
* Replacing single placeholder with multiple elements of the same type
* Helper functions, that come handy when building nested elements from DB resultset
