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

export const bracketConstants = {
	teamHeight: 28,
	teamGap: 20,

	defaultMatchGap: 20,
	depth4MatchGap: 12,
	depth5MatchGap: 4,

	twoRoundHeight: 300,
	threeRoundHeight: 300,
	fourRoundHeight: 544,
	fiveRoundHeight: 806,
	sixRoundHeight: 1100,

	bracketWidths: [
		800, // 0 rounds
		800, // 1 rounds
		800, // 2 round
		800, // 3 rounds
		985, // 4 rounds
		1023, // 5 rounds
		1137, // 6 rounds
	],

	bracketLogoBottom: [
		-250, // 0 rounds
		-250, // 1 round
		-250, // 2 rounds
		-300, // 3 rounds
		-300, // 4 rounds
		-270, // 5 rounds
		-320, // 6 rounds
	],

	winnerContainerBottom: [
		100, // 0 rounds
		100, // 1 round
		100, // 2 rounds
		100, // 3 rounds
		150, // 4 rounds
		190, // 5 rounds
		320, // 6 rounds
	],

	bracketActionsMarginTop: [
		220, // 0 rounds
		220, // 1 round
		220, // 2 rounds
		220, // 3 rounds
		94, // 4 rounds
		50, // 5 rounds
		20, // 6 rounds
	],

	// Window breakpoints
	paginatedBracketWidth: 768,
	roundWidth: 54,
}