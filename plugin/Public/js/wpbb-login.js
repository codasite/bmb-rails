;(function ($) {
  ;('use strict')
  $(function () {
    // Add a heading to the login form
    const $h1 = $('#login h1')
    $h1.append(get_heading_text())

    // Change the register button value to "Create Account"
    const registerSubmit = $("#registerform input[type='submit']")
    registerSubmit.val('Create Account')
  })

  function get_heading_text() {
    const urlParams = new URLSearchParams(window.location.search)
    let headingText = '' // Heading text for the login page
    const action = urlParams.get('action')
    switch (action) {
      case 'register':
        headingText = 'Join the Team'
        break
      case null:
        const checkemail = urlParams.get('checkemail')
        const loggedout = urlParams.get('loggedout')
        if (checkemail === null && loggedout === null) {
          headingText = 'Welcome Back!'
        }
        break
      default:
        headingText = '' // Dont show heading text in case action is something else
        break
    }

    return headingText
  }
})(jQuery)
