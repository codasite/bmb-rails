import React, { } from 'react';
import { Round, MatchNode } from '../models/MatchTree';
import { BracketLines } from './BracketLines'
import {
	getFirstRoundMatchGap,
	getMatchGap,
} from '../utils'
import { Nullable } from '../../../utils/types';
import { BracketProps } from './types';


export const DefaultBracket = (props: BracketProps) => {
	const {
		targetHeight,
		teamHeight,
		teamGap,
		matchTree,
		setMatchTree,
		// darkMode,
		// bracketName,
		MatchColumnComponent,
		MatchBoxComponent,
		TeamSlotComponent,
	} = props

	const getMatchColumns = (rounds: Nullable<MatchNode>[][], position: string, firstRoundMatchGap: number, matchHeight: number): JSX.Element[] => {
		const matchColumns = rounds.map((matches, i) => {
			const matchGap = getMatchGap(firstRoundMatchGap, matchHeight, i)
			return (
				<MatchColumnComponent
					matches={matches}
					position={position}
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
		const firstRoundMatchGap = getFirstRoundMatchGap(targetHeight, matchTree.rounds.length, matchHeight)

		const leftMatchColumns = getMatchColumns(leftMatches, 'left', firstRoundMatchGap, matchHeight)
		const rightMatchColumns = getMatchColumns(rightMatches, 'right', firstRoundMatchGap, matchHeight)
		const finalMatchColumn = getMatchColumns([finalMatch], 'center', firstRoundMatchGap, matchHeight)

		return [
			...leftMatchColumns,
			...finalMatchColumn,
			...rightMatchColumns
		]
	}

	return (
		<div className='tw-flex tw-w-full tw-justify-between'>
			{buildMatches(matchTree.rounds)}
			<BracketLines
				rounds={matchTree.rounds}
				darkMode={true}
			/>
		</div>
	)
};