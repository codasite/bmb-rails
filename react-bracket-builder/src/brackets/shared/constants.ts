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
    1137, // 7 rounds
  ],

  bracketHeights: [
    300, // 0 rounds
    300, // 1 round
    300, // 2 rounds
    300, // 3 rounds
    544, // 4 rounds
    806, // 5 rounds
    1100, // 6 rounds
    1100, // 7 rounds
  ],

  teamHeights: [
    28, // 0 rounds
    28, // 1 round
    28, // 2 rounds
    28, // 3 rounds
    28, // 4 rounds
    24, // 5 rounds
    24, // 6 rounds
    24, // 7 rounds
  ],

  teamWidths: [
    115, // 0 rounds
    115, // 1 round
    115, // 2 rounds
    115, // 3 rounds
    115, // 4 rounds
    94, // 5 rounds
    94, // 6 rounds
    94, // 7 rounds
  ],

  teamGaps: [
    20, // depth 0
    20, // depth 1
    20, // depth 2
    20, // depth 3
    12, // depth 4
    6, // depth 5
    6, // depth 6
  ],

  firstRoundsMatchGaps: [
    80, // 0 rounds
    80, // 1 round
    80, // 2 rounds
    80, // 3 rounds
    80, // 4 rounds
    58, // 5 rounds
    12, // 6 rounds
    12, // 7 rounds
  ],

  winnerContainerTopMargin: [
    0, // 0 rounds
    0, // 1 round
    0, // 2 rounds
    0, // 3 rounds
    0, // 4 rounds
    20, // 5 rounds
    100, // 6 rounds
    100, // 7 rounds
  ],

  winnerContainerMinHeight: [
    0, // 0 rounds
    0, // 1 round
    0, // 2 rounds
    0, // 3 rounds
    0, // 4 rounds
    150, // 5 rounds
    225, // 6 rounds
    225, // 7 rounds
  ],

  winnerContainerBottomMargin: [
    95, // 0 rounds
    95, // 1 round
    95, // 2 rounds
    95, // 3 rounds
    95, // 4 rounds
    250, // 5 rounds
    250, // 6 rounds
    250, // 7 rounds
  ],

  winnerContainerTitleMaxWidth: [
    700, // 0 rounds
    700, // 1 round
    700, // 2 rounds
    700, // 3 rounds
    700, // 4 rounds
    700, // 5 rounds
    500, // 6 rounds
    500, // 7 rounds
  ],

  logoContainerBottomMargin: [
    0, // 0 rounds
    0, // 1 round
    0, // 2 rounds
    0, // 3 rounds
    20, // 4 rounds
    160, // 5 rounds
    160, // 6 rounds
    160, // 7 rounds
  ],

  logoContainerMinHeight: [
    0, // 0 rounds
    0, // 1 round
    0, // 2 rounds
    0, // 3 rounds
    200, // 4 rounds
    375, // 5 rounds
    425, // 6 rounds
    425, // 7 rounds
  ],

  bracketActionsMarginTop: [
    220, // 0 rounds
    220, // 1 round
    220, // 2 rounds
    220, // 3 rounds
    94, // 4 rounds
    50, // 5 rounds
    20, // 6 rounds
    20, // 7 rounds
  ],
}

export const flexBracketConstants = {
  teamBreakpoints: [24, 48, 999],
  teamHeights: [24, 12, 6],
  teamGaps: [8, 4, 4],
  matchGapMin: [24, 8, 4],
  matchGapMax: [24, 8, 8],
}
