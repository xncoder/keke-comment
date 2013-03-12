// Edit to suit your needs.
var ADAPT_CONFIG = {
  // Where is your CSS?
  path: SKIN_PATH+'/css/layout/',

  // false = Only run once, when page first loads.
  // true = Change on window resize and page tilt.
  dynamic: false,

  // First range entry is the minimum.
  // Last range entry is the maximum.
  // Separate ranges by "to" keyword.
  range: [
    '0px    to 760px  = mobile.min.css',
    '760px  to 980px  = 720.min.css',
    '980px  to 1280px = 960.min.css',
    '1280px to 1600px = 1200.min.css',
    '1600px to 1920px = 1560.min.css',
    '1920px           = fluid.min.css'
  ]

};