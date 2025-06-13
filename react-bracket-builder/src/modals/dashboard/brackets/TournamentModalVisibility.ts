// Modal visibility state for tournament modals
// Each property represents whether a specific modal is visible
export type TournamentModalVisibility = {
  /** Modal for editing bracket details */
  editBracket: boolean
  /** Modal for sharing bracket */
  shareBracket: boolean
  /** Modal for deleting bracket */
  deleteBracket: boolean
  /** Modal for setting tournament fee */
  setTournamentFee: boolean
  /** Modal for locking live tournament */
  lockLiveTournament: boolean
  /** Modal for additional options */
  moreOptions: boolean
  /** Modal for publishing bracket */
  publishBracket: boolean
  /** Modal for completing a voting round */
  completeRound: boolean
}
