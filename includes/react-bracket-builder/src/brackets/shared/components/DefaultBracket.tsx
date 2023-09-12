import React, { useContext } from 'react';
import { Round, MatchNode } from '../models/MatchTree';
import { BracketLines, RootMatchLines } from './BracketLines'
import {
	getFirstRoundMatchGap,
	getMatchGap,
} from '../utils'
import { Nullable } from '../../../utils/types';
import { BracketProps } from './types';
import { DarkModeContext } from '../context';


export const DefaultBracket = (props: BracketProps) => {
	const {
		height,
		width,
		teamHeight,
		teamGap,
		matchTree,
		setMatchTree,
		MatchColumnComponent,
		MatchBoxComponent,
		TeamSlotComponent,
	} = props

	const darkMode = useContext(DarkModeContext);

	const getMatchColumns = (rounds: Nullable<MatchNode>[][], position: string, firstRoundMatchGap: number, matchHeight: number): JSX.Element[] => {
		const matchColumns = rounds.map((matches, i) => {
			const matchGap = getMatchGap(firstRoundMatchGap, matchHeight, i)
			return (
				<MatchColumnComponent
					matches={matches}
					matchPosition={position}
					matchTree={matchTree}
					setMatchTree={setMatchTree}
					MatchBoxComponent={MatchBoxComponent}
					TeamSlotComponent={TeamSlotComponent}
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

	const buildMatches = (rounds: Round[]) => {
		const sideMatches = rounds.slice(0, rounds.length - 1)
		const leftMatches = sideMatches.map((round) => round.matches.slice(0, round.matches.length / 2))
		const rightMatches = sideMatches.map((round) => round.matches.slice(round.matches.length / 2))
		const finalMatch = rounds[rounds.length - 1].matches

		const matchHeight = teamHeight * 2 + teamGap
		const firstRoundMatchGap = getFirstRoundMatchGap(height, matchTree.rounds.length, matchHeight)

		const leftMatchColumns = getMatchColumns(leftMatches, 'left', firstRoundMatchGap, matchHeight)
		const rightMatchColumns = getMatchColumns(rightMatches, 'right', firstRoundMatchGap, matchHeight)
		const finalMatchColumn = getMatchColumns([finalMatch], 'center', firstRoundMatchGap, matchHeight)

		return [
			...leftMatchColumns,
			...finalMatchColumn,
			...rightMatchColumns
		]
	}
	const lineStyle = {
		className: `!tw-border-t-${darkMode ? 'white' : 'dd-blue'}`,
		delay: true,
	}


	return (
		<div className={`tw-flex tw-justify-between tw-relative tw-w-[${width}px]`}>
			{buildMatches(matchTree.rounds)}
			<BracketLines
				rounds={matchTree.rounds}
				style={lineStyle}
			/>
			<RootMatchLines
				rounds={matchTree.rounds}
				style={lineStyle}
			/>
		</div>
	)
};