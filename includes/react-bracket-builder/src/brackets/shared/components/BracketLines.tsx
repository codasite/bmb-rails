import React from 'react';
import { Round, MatchNode } from '../models/MatchTree';
import LineTo, { SteppedLineTo } from 'react-lineto';
import {
	getUniqueTeamClass,
} from '../utils'

interface BracketLinesProps {
	rounds: Round[]
	darkMode?: boolean
}

export const BracketLines = (props: BracketLinesProps) => {
	const {
		rounds,
		darkMode,
	} = props
	// Helper function to create a SteppedLineTo JSX Element
	const createSteppedLine = (
		team1: string,
		team2: string,
		leftSide: boolean,
		fromAnchor: string,
		toAnchor: string,
		style: object
	): JSX.Element => (
		<SteppedLineTo
			from={leftSide ? team1 : team2} // Lines must be drawn from left to right to render properly
			to={leftSide ? team2 : team1}
			fromAnchor={fromAnchor}
			toAnchor={toAnchor}
			orientation='h'
			// within='wpbb-bracket-lines-container'
			{...style}
		/>
	);

	// Function to handle the match side and draw the lines
	// This function takes in the match details, team details, anchor details and style, 
	// and returns an array of JSX elements for the lines to be drawn for a match
	const handleMatchSide = (
		match: MatchNode,
		roundIdx: number,
		matchIdx: number,
		side: keyof MatchNode,
		team: string,
		leftSide: boolean,
		fromAnchor: string,
		toAnchor: string,
		style: object
	): JSX.Element[] => {
		if (match[side]) {
			const team1 = getUniqueTeamClass(roundIdx + 1, matchIdx * 2 + (side === 'right' ? 1 : 0), true);
			const team2 = getUniqueTeamClass(roundIdx + 1, matchIdx * 2 + (side === 'right' ? 1 : 0), false);

			return [
				createSteppedLine(team1, team, leftSide, fromAnchor, toAnchor, style),
				createSteppedLine(team2, team, leftSide, fromAnchor, toAnchor, style),
			];
		}

		return [];
	};

	// Main function
	const renderLines = (rounds: Round[]): JSX.Element[] => {
		let lines: JSX.Element[] = [];
		// Lines are always drawn from left to right so these two variables never change for horizontal lines
		const fromAnchor = 'right';
		const toAnchor = 'left';
		const style = {
			className: `wpbb-bracket-line${darkMode ? ' wpbb-dark-mode' : ''}`,
			delay: true,
			// borderColor: darkMode ? '#FFFFFF' : darkBlue,
			// borderStyle: 'solid',
			// borderWidth: 1,
		};

		rounds.forEach((round, roundIdx) => {
			round.matches.forEach((match, matchIdx) => {
				if (!match) {
					return;
				}

				const team1 = getUniqueTeamClass(roundIdx, matchIdx, true)
				const team2 = getUniqueTeamClass(roundIdx, matchIdx, false)
				// Whether the matches appear on the left or right side of the bracket
				// This determines the direction of the lines
				const team1LeftSide = matchIdx < round.matches.length / 2;
				// The second team in the last match of the last round is on the opposite side
				const team2LeftSide = roundIdx === 0 && matchIdx === 0 ? !team1LeftSide : team1LeftSide;

				lines = [
					...lines,
					...handleMatchSide(match, roundIdx, matchIdx, 'left', team1, team1LeftSide, fromAnchor, toAnchor, style),
					...handleMatchSide(match, roundIdx, matchIdx, 'right', team2, team2LeftSide, fromAnchor, toAnchor, style),
				];
				if (roundIdx === 0) {
					// Render lines for the final match
					lines = [...lines, <LineTo
						from={team1}
						to={team2}
						fromAnchor='bottom'
						toAnchor='top'
						// within='wpbb-bracket-lines-container'
						{...style}
					/>,
					<LineTo
						from='wpbb-final-winner'
						to={team1}
						fromAnchor='bottom'
						toAnchor='top'
						// within='wpbb-bracket-lines-container'
						{...style}
					/>,
					];
				}
			});
		});
		return lines;
	};
	return (
		<div className='wpbb-bracket-lines-container'>
			{renderLines(rounds)}
		</div>
	)
}