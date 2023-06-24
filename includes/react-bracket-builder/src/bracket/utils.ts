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

export const getMatchHeight = (depth: number) => {
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

export const getTeamClassName = (roundIndex, matchIndex, left) => {
	const className = `wpbb-team${left ? '1' : '2'} wpbb-team-${roundIndex}-${matchIndex}-${left ? 'left' : 'right'}`
	return className
}