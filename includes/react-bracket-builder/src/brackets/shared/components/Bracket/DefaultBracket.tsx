import React, { useContext } from 'react';
import { Round, MatchNode } from '../../models/MatchTree';
import { BracketLines, RootMatchLines } from './BracketLines'
import {
	getFirstRoundMatchGap,
	getMatchGap,
	getBracketHeight,
	getBracketWidth,
} from '../../utils'
import { Nullable } from '../../../../utils/types';
import { BracketProps } from '../types';
import { DarkModeContext } from '../../context';
import { DefaultMatchColumn } from '../MatchColumn/DefaultMatchColumn';
import { DefaultMatchBox } from '../MatchBox/DefaultMatchBox';
import { DefaultTeamSlot } from '../TeamSlot';
import { bracketConstants } from '../../constants';

export const DefaultBracket = (props: BracketProps) => {
	const {
		getHeight = getBracketHeight,
		getWidth = getBracketWidth,
		getTeamHeight = () => bracketConstants.teamHeight,
		getTeamGap = () => bracketConstants.teamGap,
		matchTree,
		setMatchTree,
		MatchColumnComponent = DefaultMatchColumn,
		MatchBoxComponent = DefaultMatchBox,
		TeamSlotComponent = DefaultTeamSlot,
		onTeamClick,
		lineStyle,
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
					// teamGap={teamGap}
					// teamHeight={teamHeight}
					onTeamClick={onTeamClick}
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

		const height = getHeight(rounds.length)
		const teamHeight = getTeamHeight(rounds.length - 1)
		const teamGap = getTeamGap(rounds.length - 1)
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
	const linesStyle = lineStyle || {
		className: `!tw-border-t-${darkMode ? 'white' : 'dd-blue'}`,
	}

	const width = getWidth(matchTree.rounds.length)

	return (
		<div className={`tw-flex tw-justify-between tw-relative tw-w-[${width}px]`}>
			{buildMatches(matchTree.rounds)}
			<BracketLines
				rounds={matchTree.rounds}
				style={linesStyle}
			/>
			<RootMatchLines
				rounds={matchTree.rounds}
				style={linesStyle}
			/>
		</div>
	)
};