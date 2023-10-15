// const teamHeight = 20

// const defaultMatchGap = 20
// const depth4MatchGap = 12
// const depth5MatchGap = 4

// const twoRoundHeight = 300
// const threeRoundHeight = 300
// const fourRoundHeight = 544
// const fiveRoundHeight = 806
// const sixRoundHeight = 1100

// Direction enum
export enum Direction {
  TopLeft = 0,
  TopRight = 1,
  Center = 2,
  BottomLeft = 3,
  BottomRight = 4,
}

export const defaultBracketConstants = {
  bracketWidths: [
    600, // 0 rounds
    600, // 1 rounds
    700, // 2 round
    800, // 3 rounds
    985, // 4 rounds
    1023, // 5 rounds
    1137, // 6 rounds
  ],

  bracketHeights: [
    300, // 0 rounds
    300, // 1 round
    300, // 2 rounds
    300, // 3 rounds
    544, // 4 rounds
    806, // 5 rounds
    1100, // 6 rounds
  ],

  teamHeights: [
    28, // 0 rounds
    28, // 1 round
    28, // 2 rounds
    28, // 3 rounds
    28, // 4 rounds
    20, // 5 rounds
    20, // 6 rounds
  ],

  teamWidths: [
    115, // 0 rounds
    115, // 1 round
    115, // 2 rounds
    115, // 3 rounds
    115, // 4 rounds
    87, // 5 rounds
    87, // 6 rounds
  ],

  teamGaps: [
    20, // depth 0
    20, // depth 1
    20, // depth 2
    20, // depth 3
    10, // depth 4
    4, // depth 5
  ],

  firstRoundsMatchGaps: [
    80, // 0 rounds
    80, // 1 round
    80, // 2 rounds
    80, // 3 rounds
    80, // 4 rounds
    58, // 5 rounds
    10, // 6 rounds
  ],

  winnerContainerBottomMargin: [
    95, // 0 rounds
    95, // 1 round
    95, // 2 rounds
    95, // 3 rounds
    95, // 4 rounds
    250, // 5 rounds
    250, // 6 rounds
  ],

  // Window breakpoints
  paginatedBracketWidth: 768,
  roundWidth: 54,

  bracketActionsMarginTop: [
    220, // 0 rounds
    220, // 1 round
    220, // 2 rounds
    220, // 3 rounds
    94, // 4 rounds
    50, // 5 rounds
    20, // 6 rounds
  ],
}

export const flexBracketConstants = {
  teamBreakpoints: [24, 48, 999],
  teamHeights: [24, 12, 6],
  teamGaps: [8, 4, 4],
  matchGapMin: [24, 8, 4],
  matchGapMax: [24, 8, 8],
}
