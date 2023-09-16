import { bracketConstants } from "./constants"
// import { BracketRes, TeamRes, MatchRes, RoundRes } from "./api/types/bracket"

const {
	teamHeight,
	defaultMatchGap,
	depth4MatchGap,
	depth5MatchGap,
	twoRoundHeight,
	threeRoundHeight,
	fourRoundHeight,
	fiveRoundHeight,
	sixRoundHeight,
	bracketWidths,
} = bracketConstants

const targetRoundHeights = [
	fourRoundHeight,
	twoRoundHeight,
	threeRoundHeight,
	fourRoundHeight,
	fiveRoundHeight,
	sixRoundHeight,
]

export const getBracketHeight = (numRounds: number) => {
	return targetRoundHeights[numRounds - 1]
}

export const getBracketWidth = (numRounds: number) => {
	return bracketWidths[numRounds]
}


export const getMatchBoxHeight = (depth: number) => {
	let gap = teamHeight
	if (depth === 4) {
		gap += depth4MatchGap
	} else if (depth === 5) {
		gap += depth5MatchGap
	} else {
		gap += defaultMatchGap
	}
	return gap
}

export const getTeamClasses = (roundIndex: number, matchIndex: number, left: boolean) => {
	const uniqueClass = getUniqueTeamClass(roundIndex, matchIndex, left ? 'left' : 'right')
	const teamClass = getTeamClass(left)
	const className = `${teamClass} ${uniqueClass}`
	return className
}

const getTeamClass = (left: boolean) => {
	const teamClass = `wpbb-team${left ? '1' : '2'}`
	return teamClass
}

export const getUniqueTeamClass = (roundIndex: number, matchIndex: number, position: string) => {
	const className = `wpbb-team-${roundIndex}-${matchIndex}-${position}`
	return className
}


/**
 * Get the match height for the first round of a bracket given a target height
 */
export const getFirstRoundMatchHeight = (targetHeight, numDirections, numRounds, teamHeight) => {
	const maxMatchesPerRound = 2 ** (numRounds - 1)
	const maxMatchesPerColumn = maxMatchesPerRound / numDirections
	let firstRoundMatchHeight = targetHeight / maxMatchesPerColumn
	firstRoundMatchHeight += (firstRoundMatchHeight - teamHeight) / maxMatchesPerColumn // Divvy up spacing that would be added after the last match in the column
	return firstRoundMatchHeight
}

/**
 * Get the match gap for the first round of a bracket given a target height
 * 
 */
export const getFirstRoundMatchGap = (targetHeight: number, numRounds: number, matchHeight: number) => {
	const numDirections = 2
	const firstRoundMatches = 2 ** (numRounds - 1) / numDirections
	const firstRoundGaps = firstRoundMatches - 1
	const firstRoundMatchGap = (targetHeight - firstRoundMatches * matchHeight) / firstRoundGaps
	return firstRoundMatchGap
}

/**
 * Get the match height for a subsequent round of a bracket given the first round match height
 * @param {number} firstRoundMatchHeight The match height for the first round of a bracket
 * @param {number} i The index of the round when building up from the first round
 */
export const getTargetMatchHeight = (firstRoundMatchHeight, i) => {
	return firstRoundMatchHeight * (2 ** i)
}

/**
 * Get the match gap for a subsequent round of a bracket given the first round match gap
 * @param {number} firstRoundMatchGap The match height for the first round of a bracket
 * @param {number} matchHeight The height of the match
 * @param {number} i The index of the round when building up from the first round
 */
export const getMatchGap = (firstRoundMatchGap: number, matchHeight: number, i: number) => {
	const power = 2 ** i
	return firstRoundMatchGap * power + (power - 1) * matchHeight
}

/**
 * Bitwise operation to check if a number is a power of 2
 */
export const isPowerOfTwo = (num: number) => {
	return (num & (num - 1)) === 0
}