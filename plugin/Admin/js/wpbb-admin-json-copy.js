document.addEventListener('DOMContentLoaded', function () {
  const jsonMetaBox = document.getElementById('wpbb_bracket_json')
  if (!jsonMetaBox) return

  // Create and add the copy button
  const copyButton = document.createElement('button')
  copyButton.className = 'wpbb-json-copy-button'
  copyButton.textContent = 'Copy JSON'
  copyButton.type = 'button' // Explicitly set button type to prevent form submission
  jsonMetaBox.appendChild(copyButton)

  // Handle click event
  copyButton.addEventListener('click', async function (e) {
    // Prevent default button behavior
    e.preventDefault()
    e.stopPropagation()

    try {
      // Get the JSON content from the meta box
      const jsonContent = jsonMetaBox.querySelector('pre').textContent

      // Copy to clipboard
      await navigator.clipboard.writeText(jsonContent)

      // Visual feedback
      copyButton.textContent = 'Copied!'
      copyButton.classList.add('copied')

      // Reset button after 2 seconds
      setTimeout(() => {
        copyButton.textContent = 'Copy JSON'
        copyButton.classList.remove('copied')
      }, 2000)
    } catch (err) {
      console.error('Failed to copy text: ', err)
      copyButton.textContent = 'Failed to copy'
      copyButton.style.background = '#d63638'

      // Reset button after 2 seconds
      setTimeout(() => {
        copyButton.textContent = 'Copy JSON'
        copyButton.style.background = ''
      }, 2000)
    }
  })
})
