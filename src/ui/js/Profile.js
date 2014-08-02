// namespace for this "module"
var Profile = {};

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Profile.showProfilePage() is the landing function. Always call
// this first. It sets up #profile_page and calls Profile.getProfile()
// * Profile.getProfile() calls the API, setting Api.profile_info. It calls
//   Profile.showPage()
// * Profile.showPage() uses the data returned by the API to build
//   the contents of the page as Profile.page and calls Profile.arrangePage()
// * Profile.arrangePage() sets the contents of <div id="profile_page"> on the
//   live page
////////////////////////////////////////////////////////////////////////

Profile.showProfilePage = function() {

  // Setup necessary elements for displaying status messages
  Env.setupEnvStub();

  // Make sure the div element that we will need exists in the page body
  if ($('#profile_page').length === 0) {
    $('body').append($('<div>', {'id': 'profile_page', }));
  }

  // Get all needed information, then display Profile page
  Profile.getProfile(Profile.showPage);
};

Profile.getProfile = function(callback) {
  var playerName = Env.getParameterByName('player');

  if (Login.logged_in) {
    Api.loadProfileInfo(playerName, callback);
  } else {
    return callback();
  }
};

Profile.showPage = function() {
  Profile.page = $('<div>');

  if (!Login.logged_in) {
    Env.message = {
      'type': 'error',
      'text': 'Can\'t view player profile because you are not logged in',
    };
  } else if (Api.profile_info.load_status != 'ok') {
    if (Env.message === undefined || Env.message === null) {
      Env.message = {
        'type': 'error',
        'text': 'An internal error occurred while loading the profile info.',
      };
    }
  } else {
    Profile.page.append(Profile.buildProfileTable());
  }

  // Actually layout the page
  Profile.arrangePage();
};

Profile.arrangePage = function() {
  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  $('#profile_page').empty();
  $('#profile_page').append(Profile.page);
};

////////////////////////////////////////////////////////////////////////
// Helper routines to add HTML entities to existing pages

Profile.buildProfileTable = function() {
  var table = $('<table>', { 'class': 'profileTable', });

  var thead = $('<thead>');
  table.append(thead);
  thead.append(Profile.buildProfileTableRow('Profile',
    Api.profile_info.name_ingame, 'unknown'));

  var tbody = $('<tbody>');
  table.append(tbody);

  var birthday = null;
  if (Api.profile_info.dob_month !== 0 && Api.profile_info.dob_day !== 0) {
    birthday = Api.MONTH_NAMES[Api.profile_info.dob_month] + ' ' +
      Api.profile_info.dob_day;
  }

  var challengeLinkHolder = null;
  if (Login.player != Api.profile_info.name_ingame) {
    challengeLinkHolder = $('<span>');
    challengeLinkHolder.append($('<a>', {
      'href':
        'create_game.html?opponent=' +
        encodeURIComponent(Api.profile_info.name_ingame),
      'text': 'Create game!',
    }));
    if (Api.profile_info.favorite_button) {
      challengeLinkHolder.append(' ');
      challengeLinkHolder.append($('<a>', {
        'href':
          'create_game.html?opponent=' +
          encodeURIComponent(Api.profile_info.name_ingame) +
          '&opponentButton=' +
          encodeURIComponent(Api.profile_info.favorite_button),
        'text': 'With ' + Api.profile_info.favorite_button + '!',
      }));
    }
  }

  var record = Api.profile_info.n_games_won + '/' +
    Api.profile_info.n_games_lost + ' (W/L)';

  var gamesLinksHolder = $('<span>');
  gamesLinksHolder.append($('<a>', {
    'text': 'Active',
    'href':
      Env.ui_root + 'history.html#!playerNameA=' +
      Api.profile_info.name_ingame + '&status=ACTIVE',
  }));
  gamesLinksHolder.append(' ');
  gamesLinksHolder.append($('<a>', {
    'text': 'Completed',
    'href':
      Env.ui_root + 'history.html#!playerNameA=' +
      Api.profile_info.name_ingame + '&status=COMPLETE',
  }));

  var commentHolder = null;
  if (Api.profile_info.comment) {
    commentHolder = $('<span>');
    var cookedComment = Env.prepareRawTextForDisplay(Api.profile_info.comment);
    commentHolder.append(cookedComment);
  }

  var homepageLink = null;
  if (Api.profile_info.homepage) {
    var homepageUrl = Env.validateUrl(Api.profile_info.homepage);
    if (homepageUrl) {
      homepageLink = $('<a>', {
        'text': homepageUrl,
        'href': homepageUrl,
        'target': '_blank',
      });
    } else {
      homepageLink = $('<a>', {
        'text': 'INVALID URL',
        'href': 'javascript:alert("Homepage URL was invalid")',
        'target': '_blank',
      });
    }
  }

  var solipsismAlternatives = [
    'solipsism overflow',
    'autoludic prohibition',
    'cloning tanks offline',
    'tu ipse es',
    'can\'t. shan\'t. won\'t.',
    'on your own? no',
    'it\'d never work out',
    'solitaire unavailable',
    'try another castle',
    'expand your search',
    'you and your shadow?',
    'mirror match = mistake',
    'two\'s company; one\'s not',
    'other people exist!',
    'you know you too well',
    'isn\'t that cheating?',
    'you\'d probably lose',
    'you\'d obviously win',
    'it\'d just be a draw',
    'let others play you',
    'the loneliest number',
    'are you twins?',
    'one hand clapping',
    '1 + 0 != 2',
    'not yourself, silly',
    'I\'m sorry, Dave...',
    'ceci n\'est pas une erreur',
    'the site doesn\'t like that',
    'would summon Cthulhu',
    'spatio-temporal paradox',
    'bilocate much?',
    'looking out for #1?'
  ];
  var solipsindex = Math.floor(Math.random() * solipsismAlternatives.length);
  var solipsismNotification = solipsismAlternatives[solipsindex];

  tbody.append(Profile.buildProfileTableRow('Real name',
    Api.profile_info.name_irl, 'unknown', true));
  tbody.append(Profile.buildProfileTableRow('Record', record, 'none', true));
  tbody.append(Profile.buildProfileTableRow('Birthday', birthday, 'unknown',
    true));
  if (Api.profile_info.gender) {
    tbody.append(Profile.buildProfileTableRow('Gender',
      Api.profile_info.gender, 'irrelevant', true));
  }
  tbody.append(Profile.buildProfileTableRow('Email address',
    Api.profile_info.email, 'private', true));
  tbody.append(Profile.buildProfileTableRow('Member since',
    Env.formatTimestamp(Api.profile_info.creation_time, 'date'), 'unknown',
    true));
  tbody.append(Profile.buildProfileTableRow('Last visit',
    Env.formatTimestamp(Api.profile_info.last_access_time, 'date'), 'never',
    true));
  tbody.append(Profile.buildProfileTableRow('Games', gamesLinksHolder, '',
    true));
  tbody.append(Profile.buildProfileTableRow('Favorite button',
    Api.profile_info.favorite_button, 'undecided', true));
  tbody.append(Profile.buildProfileTableRow('Favorite button set',
    Api.profile_info.favorite_buttonset, 'unselected', true));
  tbody.append(Profile.buildProfileTableRow(
    'Challenge ' + Api.profile_info.name_ingame + ' to a game',
    challengeLinkHolder, solipsismNotification, false));
  tbody.append(Profile.buildProfileTableRow('Homepage',
    homepageLink, 'homeless', false));
  tbody.append(Profile.buildProfileTableRow('Comment',
    commentHolder, 'none', false));

  if (!Env.getCookieNoImages()) {
    var url;
    if (Api.profile_info.uses_gravatar) {
      url = 'http://www.gravatar.com/avatar/' + Api.profile_info.email_hash;
      if (Api.profile_info.image_size) {
        url += '?s=' + Api.profile_info.image_size;
      }
    } else {
      url = Env.ui_root + 'images/no-image.png';
    }
    var image = $('<img>', {
      'src': url,
      'class': 'profileImage',
    });

    var partialTds = table.find('td.partialValue');

    var imageTd = $('<td>', { 'class': 'partialValue', 'rowspan': '9', });
    partialTds.first().parent().append(imageTd);
    imageTd.append(image);
  }

  return table;
};

Profile.buildProfileTableRow = function(
    label, value, missingValue, shrinkable) {
  var valueClass = (shrinkable ? 'partialValue' : 'value');
  var tr = $('<tr>');
  tr.append($('<td>', { 'text': label + ':', 'class': 'label' }));
  if (value) {
    if (value instanceof jQuery) {
      tr.append($('<td>', {
        'class': valueClass,
        'colspan': (shrinkable ? '1': '2'),
      }).append(value));
    } else {
      tr.append($('<td>', {
        'text': value,
        'class': valueClass,
        'colspan': (shrinkable ? '1': '2'),
      }));
    }
  } else {
    tr.append($('<td>', {
      'text': missingValue,
      'class': 'missingValue ' + valueClass,
      'colspan': (shrinkable ? '1': '2'),
    }));
  }
  return tr;
};

// Takes a URL that was entered by a user and returns a version of it that's
// safe to insert into an anchor tag (or returns NULL if we can't sensibly do
// that).
// Based in part on advice from http://stackoverflow.com/questions/205923
Profile.validateUrl = function(url) {
  // First, check for and reject anything with inappropriate characters
  // (We can expand this list later if it becomes necessary)
  if (!url.match(/^[-A-Za-z0-9+&@#/%?=~_!:,.\(\)]+$/)) {
    return null;
  }

  // Then ensure that it begins with http:// or https://
  if (url.toLowerCase().indexOf('http://') !== 0 &&
      url.toLowerCase().indexOf('https://') !== 0) {
    url = 'http://' + url;
  }

  // This should create a relatively safe URL. It does not verify that it's a
  // *valid* URL, but if it is invalid, this should at least render it impotent.
  // This also doesn't verify that the URL points to a safe page, but that is
  // outside of the scope of this function.
  return url;
};

