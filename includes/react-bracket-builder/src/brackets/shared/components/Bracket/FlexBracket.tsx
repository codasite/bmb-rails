import React, { useContext } from 'react';
import { Round, MatchNode, MatchTree } from '../../models/MatchTree';
import { BracketLines, RootMatchLines } from './BracketLines'
import {
	getBracketHeight,
	getBracketWidth,
} from '../../utils'
import { Nullable } from '../../../../utils/types';
import { BracketProps } from '../types';
import { DarkModeContext } from '../../context';
import { FlexMatchColumn } from '../MatchColumn'
import { FlexMatchBox } from '../MatchBox'
import { FlexTeamSlot } from '../TeamSlot'
import { bracketConstants } from '../../constants';

const teamBreakpoints = [
	24,
	48,
	999,
]

const teamHeights = [
	24,
	12,
	6,
]

const teamGaps = [
	8,
	4,
	4,
]

const matchGaps = [
	24,
	12,
	12,
]


export const FlexBracket = (props: BracketProps) => {
	const {
		getHeight = getBracketHeight,
		getWidth = getBracketWidth,
		getTeamHeight = () => bracketConstants.teamHeight,
		getTeamGap = () => bracketConstants.teamGap,
		matchTree,
		setMatchTree,
		MatchColumnComponent = FlexMatchColumn,
		onTeamClick,
	} = props

	const darkMode = useContext(DarkModeContext);

	const getMatchColumns = (
		rounds: Nullable<MatchNode>[][],
		position: string,
		matchGap: number,
		teamGap: number,
		teamHeight: number
	): JSX.Element[] => {

		const matchColumns = rounds.map((matches, i) => {
			return (
				<MatchColumnComponent
					matches={matches}
					matchPosition={position}
					matchTree={matchTree}
					onTeamClick={onTeamClick}
					setMatchTree={setMatchTree}
					matchGap={matchGap}
					teamGap={teamGap}
					teamHeight={teamHeight}
				/>
			)
		})
		if (position === 'right') {
			matchColumns.reverse()
		}

		return matchColumns
	}

	const buildMatches = (tree: MatchTree) => {
		const rounds = tree.rounds
		const sideMatches = rounds.slice(0, rounds.length - 1)
		const leftMatches = sideMatches.map((round) => round.matches.slice(0, round.matches.length / 2))
		const rightMatches = sideMatches.map((round) => round.matches.slice(round.matches.length / 2))
		const finalMatch = rounds[rounds.length - 1].matches

		const numTeams = tree.getNumTeams()
		const teamBreakpointIndex = teamBreakpoints.findIndex((breakpoint) => numTeams <= breakpoint)
		const teamHeight = teamHeights[teamBreakpointIndex]
		const teamGap = teamGaps[teamBreakpointIndex]
		const matchGap = matchGaps[teamBreakpointIndex]

		const leftMatchColumns = getMatchColumns(leftMatches, 'left', matchGap, teamGap, teamHeight)
		const rightMatchColumns = getMatchColumns(rightMatches, 'right', matchGap, teamGap, teamHeight)
		const finalMatchColumn = getMatchColumns([finalMatch], 'center', matchGap, teamGap, teamHeight)

		return [
			...leftMatchColumns,
			...finalMatchColumn,
			...rightMatchColumns
		]
	}

	return (
		<div className={`tw-flex tw-justify-between tw-h-full tw-gap-8 md:tw-gap-16 `}>
			{buildMatches(matchTree)}
		</div>
	)
};