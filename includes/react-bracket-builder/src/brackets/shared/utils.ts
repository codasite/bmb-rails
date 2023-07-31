import { bracketConstants } from "./constants"

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
} = bracketConstants

const targetRoundHeights = [
	fourRoundHeight,
	twoRoundHeight,
	threeRoundHeight,
	fourRoundHeight,
	fiveRoundHeight,
	sixRoundHeight,
]

export const getTargetHeight = (numRounds: number) => {
	return targetRoundHeights[numRounds - 1]
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
	const uniqueClass = getUniqueTeamClass(roundIndex, matchIndex, left)
	const teamClass = getTeamClass(left)
	const className = `${teamClass} ${uniqueClass}`
	return className
}

const getTeamClass = (left: boolean) => {
	const teamClass = `wpbb-team${left ? '1' : '2'}`
	return teamClass
}

export const getUniqueTeamClass = (roundIndex: number, matchIndex: number, left: boolean) => {
	const className = `wpbb-team-${roundIndex}-${matchIndex}-${left ? 'left' : 'right'}`
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
 * Get the match height for a subsequent round of a bracket given the first round match height
 * @param {number} firstRoundMatchHeight The match height for the first round of a bracket
 * @param {number} i The index of the round when building up from the first round
 */
export const getTargetMatchHeight = (firstRoundMatchHeight, i) => {
	return firstRoundMatchHeight * (2 ** i)
}
