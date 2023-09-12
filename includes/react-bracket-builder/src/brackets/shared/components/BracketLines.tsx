import React, { useContext } from 'react';
import { Round, MatchNode } from '../models/MatchTree';
import LineTo, { SteppedLineTo } from 'react-lineto';
import { DarkModeContext } from '../context';
import {
	getUniqueTeamClass,
} from '../utils'

interface BracketLinesProps {
	rounds: Round[]
}

export const BracketLines = (props: BracketLinesProps) => {
	const {
		rounds,
	} = props
	const darkMode = useContext(DarkModeContext);
	// Main function
	const renderLines = (rounds: Round[]): JSX.Element[] => {
		let lines: JSX.Element[] = [];
		// Lines are always drawn from left to right so these two variables never change for horizontal lines
		const fromAnchor = 'right';
		const toAnchor = 'left';
		const style = {
			// className: `wpbb-bracket-line${darkMode ? ' wpbb-dark-mode' : ''}`,
			// className: '!tw-border-t-white dark:!tw-border-t-dd-blue',
			className: `!tw-border-t-${darkMode ? 'white' : 'dd-blue'}`,
			delay: true,
			// borderColor: darkMode ? '#FFFFFF' : darkBlue,
			// borderStyle: 'solid',
			// borderWidth: 1,
		};

		rounds.forEach((round) => {
			round.matches.forEach((match, i, matches) => {
				if (!match) {
					return;
				}
				const {
					matchIndex,
					roundIndex,
					parent,
				} = match;

				if (!parent) {
					return;
				}
				const {
					matchIndex: parentMatchIndex,
					roundIndex: parentRoundIndex,
				} = parent;

				const matchTeam1Class = getUniqueTeamClass(roundIndex, matchIndex, 'left');
				const matchTeam2Class = getUniqueTeamClass(roundIndex, matchIndex, 'right');
				const parentTeamClass = getUniqueTeamClass(parentRoundIndex, parentMatchIndex, `${match.isLeftChild() ? 'left' : 'right'}`);

				const bracketLeft = matchIndex < matches.length / 2

				const line1FromClass = bracketLeft ? matchTeam1Class : parentTeamClass;
				const line1ToClass = bracketLeft ? parentTeamClass : matchTeam1Class;
				const line2FromClass = bracketLeft ? matchTeam2Class : parentTeamClass;
				const line2ToClass = bracketLeft ? parentTeamClass : matchTeam2Class;

				lines = [
					...lines,
					<SteppedLineTo
						from={line1FromClass}
						to={line1ToClass}
						fromAnchor={fromAnchor}
						toAnchor={toAnchor}
						orientation='h'
						{...style}
					/>,
					<SteppedLineTo
						from={line2FromClass}
						to={line2ToClass}
						fromAnchor={fromAnchor}
						toAnchor={toAnchor}
						orientation='h'
						{...style}
					/>,
				];
			});
		});
		return lines;
	};
	return (
		<div className='tw-absolute'>
			{renderLines(rounds)}
		</div>
	)
}