export const addToApparelHandler = (playId: number, redirectUrl: string) => {
  if (playId) {
    //set play id in cookie
    const expiryDate = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000) // 30 days from now
    document.cookie = `play_id=${playId}; path=/; expires=${expiryDate.toUTCString()}`
  }
  window.location.href = redirectUrl
}
