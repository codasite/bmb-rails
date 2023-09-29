import React, { useContext } from 'react';
import { Round, MatchNode } from '../../models/MatchTree';
import { BracketLines, RootMatchLines } from './BracketLines'
import {
	getFirstRoundMatchGap as getDefaultFirstRoundMatchGap,
	getMatchGap,
	getSubsequentMatchGap as getDefaultSubsequentMatchGap,
	getBracketHeight as getDefaultBracketHeight,
	getBracketWidth as getDefaultBracketWidth,
	getTeamGap as getDefaultTeamGap,
	getTeamHeight as getDefaultTeamHeight,
	getTeamFontSize as getDefaultTeamFontSize,
	getTeamWidth,
} from '../../utils'
import { Nullable } from '../../../../utils/types';
import { BracketProps } from '../types';
import { BracketMetaContext, DarkModeContext } from '../../context';
import { DefaultMatchColumn } from '../MatchColumn/DefaultMatchColumn';
import { DefaultTeamSlot } from '../TeamSlot';
import { defaultBracketConstants } from '../../constants';
import { useWindowDimensions } from '../../../../utils/hooks';
import { WinnerContainer } from '../MatchBox/Children/WinnerContainer';
import { LogoContainer } from '../MatchBox/Children/LogoContainer';

export const DefaultBracket = (props: BracketProps) => {
	const {
		getBracketHeight = getDefaultBracketHeight,
		getBracketWidth = getDefaultBracketWidth,
		getTeamHeight = getDefaultTeamHeight,
		getTeamGap = getDefaultTeamGap,
		getFirstRoundMatchGap = getDefaultFirstRoundMatchGap,
		getSubsequentMatchGap = getDefaultSubsequentMatchGap,
		getTeamFontSize = getDefaultTeamFontSize,
		matchTree,
		setMatchTree,
		MatchColumnComponent = DefaultMatchColumn,
		MatchBoxComponent,
		TeamSlotComponent = DefaultTeamSlot,
		MatchBoxChildComponent,
		onTeamClick,
		lineStyle,
		lineColor = 'dd-blue',
		darkLineColor = 'white',
		lineWidth = 1,
	} = props

	const { width: windowWidth, height: windowHeight } = useWindowDimensions()
	const darkMode = useContext(DarkModeContext);

	const getBracketMeasurements = (roundIndex: number, numRounds: number) => {
		const teamHeight = getTeamHeight(numRounds)
		const teamWidth = getTeamWidth(numRounds)
		const teamGap = getTeamGap(numRounds - roundIndex - 1)
		const matchHeight = teamHeight * 2 + teamGap
		let matchGap: number
		if (roundIndex === 0) {
			matchGap = getFirstRoundMatchGap(numRounds)
		}
		else {
			const {
				matchHeight: prevMatchHeight,
				matchGap: prevMatchGap,
			} = getBracketMeasurements(roundIndex - 1, numRounds)
			matchGap = getSubsequentMatchGap(prevMatchHeight, prevMatchGap, matchHeight)
		}

		return {
			teamHeight,
			teamWidth,
			teamGap,
			matchHeight,
			matchGap,
		}
	}

	const getMatchColumns = (rounds: Nullable<MatchNode>[][], position: string, numRounds: number): JSX.Element[] => {
		const matchColumns = rounds.map((matches, i) => {
			const roundIndex = matches.find(match => match !== null)?.roundIndex
			const {
				teamHeight,
				teamWidth,
				teamGap,
				matchGap,
			} = getBracketMeasurements(roundIndex ?? i, numRounds)

			const fontSize = getTeamFontSize(numRounds)

			return (
				<MatchColumnComponent
					matches={matches}
					matchPosition={position}
					matchTree={matchTree}
					setMatchTree={setMatchTree}
					MatchBoxComponent={MatchBoxComponent}
					TeamSlotComponent={TeamSlotComponent}
					MatchBoxChildComponent={MatchBoxChildComponent}
					matchGap={matchGap}
					teamGap={teamGap}
					teamHeight={teamHeight}
					teamWidth={teamWidth}
					teamFontSize={fontSize}
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
		// Build the left matches, right matches, and final match separately
		const numRounds = rounds.length
		const sideMatches = rounds.slice(0, numRounds - 1)
		const leftMatches = sideMatches.map((round) => round.matches.slice(0, round.matches.length / 2))
		const rightMatches = sideMatches.map((round) => round.matches.slice(round.matches.length / 2))
		const finalMatch = rounds[numRounds - 1].matches

		// const bracketHeight = getBracketHeight(numRounds)
		// const teamHeight = getTeamHeight(numRounds)
		// const firstRoundTeamGap = getTeamGap(0)
		// const firstRoundsMatchHeight = teamHeight * 2 + firstRoundTeamGap
		// const firstRoundMatchGap = getFirstRoundMatchGap(numRounds)

		const leftMatchColumns = getMatchColumns(leftMatches, 'left', numRounds)
		const rightMatchColumns = getMatchColumns(rightMatches, 'right', numRounds)
		const finalMatchColumn = getMatchColumns([finalMatch], 'center', numRounds)

		return [
			...leftMatchColumns,
			...finalMatchColumn,
			...rightMatchColumns
		]
	}
	const linesStyle = lineStyle || {
		className: `!tw-border-t-[${lineWidth}px] !tw-border-t-${darkMode ? darkLineColor : lineColor}`,
	}

	const width = getBracketWidth(matchTree.rounds.length)

	const {
		date: bracketDate,
		title: bracketTitle
	} = useContext(BracketMetaContext)

	const rootMatch = matchTree.getRootMatch()
	const numRounds = matchTree.rounds.length
	const winnerContainerMB = defaultBracketConstants.winnerContainerBottomMargin[numRounds]

	return (
		<div className='tw-flex tw-flex-col'>
			{
				rootMatch &&
				<div className={`tw-mb-[${winnerContainerMB}px]`}>

					<WinnerContainer
						match={rootMatch}
						matchTree={matchTree}
						matchPosition='center'
						TeamSlotComponent={TeamSlotComponent}
						topText={bracketTitle}
					/>
				</div>
			}
			<div className='tw-flex tw-flex-col tw-justify-center tw-h-100'>
				<div className={`tw-flex tw-justify-${numRounds > 1 ? 'between' : 'center'} tw-relative tw-w-[${width}px]`}>
					{buildMatches(matchTree.rounds)}
				</div>
			</div>
			{
				<div className={`tw-mt-${numRounds > 5 ? 50 : 20}`}>
					<LogoContainer
						{...props}
						bottomText={bracketDate}
					/>
				</div>
			}
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