  module("Profile", {
  'setup': function() {
    BMTestUtils.ProfilePre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Create the profile_page div so functions have something to modify
    if (document.getElementById('profile_page') == null) {
      $('body').append($('<div>', {'id': 'profile_page', }));
    }
  },
  'teardown': function(assert) {

    // Delete all elements we expect this module to create

    // JavaScript variables
    delete Api.profile_info;
    delete Env.window.location.search;
    delete Profile.page;

    // Page elements
    $('#profile_page').remove();
    $('#profile_page').empty();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Fail if any other elements were added or removed
    BMTestUtils.ProfilePost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.ProfilePost, BMTestUtils.ProfilePre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Profile module has been loaded
test("test_Profile_is_loaded", function(assert) {
  assert.ok(Profile, "The Profile namespace exists");
});

test("test_Profile.showProfilePage", function(assert) {
  stop();
  Profile.showProfilePage();
  var item = document.getElementById('profile_page');
  assert.equal(item.nodeName, "DIV",
        "#profile_page is a div after showProfilePage() is called");
  start();
});

test("test_Profile.getProfile", function(assert) {
  stop();
  Env.window.location.search = '?player=tester';
  Profile.getProfile(function() {
    assert.ok(Api.profile_info, "Profile info parsed from server");
    if (Api.profile_info) {
      assert.equal(Api.profile_info.load_status, 'ok',
        "Profile info parsed successfully from server");
    }
    start();
  });
});

test("test_Profile.showPage", function(assert) {
  stop();
  Env.window.location.search = '?player=tester';
  Profile.getProfile(function() {
    Profile.showPage();
    var htmlout = Profile.page.html();
    assert.ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

test("test_Profile.arrangePage", function(assert) {
  stop();
  Env.window.location.search = '?player=tester';
  Profile.getProfile(function() {
    Profile.page = $('<div>');
    Profile.page.append($('<p>', {'text': 'hi world', }));
    Profile.arrangePage();
    var item = document.getElementById('profile_page');
    assert.equal(item.nodeName, "DIV",
          "#profile_page is a div after arrangePage() is called");
    start();
  });
});

test("test_Profile.buildProfileTable", function(assert) {
  stop();
  Env.window.location.search = '?player=tester';
  Profile.getProfile(function() {
    var table = Profile.buildProfileTable();
    var htmlout = table.html();
    assert.ok(htmlout.match('February 29'), "Profile table content was generated");
    start();
  });
});

test("test_Profile.buildProfileTableRow", function(assert) {
  var tr = Profile.buildProfileTableRow('Things', 'something', 'nothing', true);
  var valueTd = tr.find('td.partialValue');
  assert.equal(valueTd.text(), 'something', 'Value should be in partialValue cell');

  var tr = Profile.buildProfileTableRow('Things', 'something', 'nothing', false);
  var valueTd = tr.find('td.value');
  assert.equal(valueTd.text(), 'something', 'Value should be in value cell');

  tr = Profile.buildProfileTableRow('Things', null, 'nothing', false);
  valueTd = tr.find('td.missingValue');
  assert.equal(valueTd.text(), 'nothing', 'Missing value should be in missingValue cell');
});
