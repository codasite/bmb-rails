import React, { useState, useEffect, useRef, createContext, useContext } from 'react';
import { Button } from 'react-bootstrap';
import { Form } from 'react-bootstrap';
import { Nullable } from '../../../utils/types';
import { MatchTree, Round, MatchNode, Team, WildcardPlacement } from '../../shared/models/MatchTree'
import { defaultBracketConstants } from '../../shared/constants';
import { BracketContext } from '../../shared/context';
// Direction enum
enum Direction {
    TopLeft = 0,
    TopRight = 1,
    Center = 2,
    BottomLeft = 3,
    BottomRight = 4,
}

export interface BracketDetials {
    numRounds: number;
}


interface MatchBoxProps {
    match: MatchNode | null;
    direction: Direction;
    height: number;
    spacing: number;
    round: Round;
    width: number;
}

const MatchBox = (props: MatchBoxProps) => {
    const {
        match,
        direction,
        height,
        spacing,
        round,
        width
    } = props
    if (match === null) {
        return (
            <div className='wpbb-match-box-empty' style={{ height: height + spacing, width: width }} />
        )
    }
    const bracket = useContext(BracketContext);

    let className: string;
    let bottom = 0;

    if (direction === Direction.TopLeft || direction === Direction.BottomLeft) {
        className = 'wpbb-match-box-left'
    } else if (direction === Direction.TopRight || direction === Direction.BottomRight) {
        className = 'wpbb-match-box-right'
    } else {
        bottom = 10;
        className = 'wpbb-match-box-center'
    }

    const upperOuter = match.left === null
    const lowerOuter = match.right === null

    if (upperOuter && lowerOuter) {
        // First round
        className += '-outer'
    } else if (upperOuter) {
        // Upper bracket
        className += '-outer-upper'
    } else if (lowerOuter) {
        // Lower bracket
        className += '-outer-lower'
    }

    const setColor = (round, match, className) => {

        if (!bracket?.numRounds) {
            return defaultBracketConstants.color2;
        }
        if (round?.depth === (bracket?.numRounds - 1)) {
            return defaultBracketConstants.color2;
        }
        else if (round?.depth === (bracket?.numRounds - 2)) {
            if (match.left == null && className == defaultBracketConstants.team1) {
                return defaultBracketConstants.color2;
            }
            else if (match.right == null && className == defaultBracketConstants.team2) {
                return defaultBracketConstants.color2;
            }
            else {
                return defaultBracketConstants.color1;
            }
        }
        return defaultBracketConstants.color1;
    }

    return (
        <div className={className} style={{ height: height, marginBottom: spacing, bottom: bottom, width: width }}>
            <div className='wpbb-team1' style={{ minWidth: width, background: setColor(round, match, 'wpbb-team1') }} > </div>
            <div className='wpbb-team2' style={{ minWidth: width, background: setColor(round, match, 'wpbb-team2') }} > </div>
        </div>
    )
}

interface MatchColumnProps {
    round: Round;
    matches: Nullable<MatchNode>[];
    direction: Direction;
    numDirections: number;
    matchHeight: number;
    matchWidth: number;
}

export const MatchColumn = (props: MatchColumnProps) => {
    const {
        round,
        matches,
        direction,
        matchHeight,
        matchWidth
    } = props

    const buildMatches = () => {
        const matchBoxes = matches.map((match, i) => {
            // const matchIndex = direction === Direction.TopLeft || direction === Direction.BottomLeft ? i : i + matches.length
            return (
                <MatchBox
                    match={match}
                    direction={direction}
                    height={matchHeight}
                    width={matchWidth}
                    spacing={i + 1 < matches.length ? matchHeight : 0} // Do not add spacing to the last match in the round column
                    round={round}
                />
            )
        })
        return matchBoxes

    }
    return (
        <div className='wpbb-round'>
            <div className='wpbb-round__body'>
                {buildMatches()}
            </div>
        </div>
    )
}


interface BracketProps {
    matchTree: MatchTree;
}

export const UserTemplatePreview = (props: BracketProps) => {
    const {
        matchTree,
    } = props

    const rounds = matchTree.rounds

    const bracketRef = useRef<HTMLDivElement>(null)

    /**	 */
    const buildRounds2 = (rounds: Round[]) => {

        const numDirections = 2

        return [
            ...rounds.slice(1).reverse().map((round, idx) => {
                // Get the first half of matches for this column
                const colMatches = round.matches.slice(0, round.matches.length / 2)

                return <BracketContext.Provider value={{ numRounds: rounds.length }}>
                    <MatchColumn
                        matches={colMatches}
                        round={round} direction={Direction.TopLeft}
                        numDirections={numDirections}
                        matchHeight={defaultBracketConstants.previewBracketHeight}
                        matchWidth={defaultBracketConstants.previewBracketWidth}
                    />
                </BracketContext.Provider>
            }),
            // handle final round differently
            <BracketContext.Provider value={{ numRounds: rounds.length }}>
                <MatchColumn
                    matches={rounds[0].matches}
                    round={rounds[0]}
                    direction={Direction.Center}
                    numDirections={numDirections}
                    matchHeight={defaultBracketConstants.previewBracketHeight}
                    matchWidth={defaultBracketConstants.previewBracketWidth}
                /></BracketContext.Provider>,
            ...rounds.slice(1).map((round, idx, arr) => {
                const colMatches = round.matches.slice(round.matches.length / 2)

                return <BracketContext.Provider value={{ numRounds: rounds.length }}><MatchColumn
                    round={round}
                    matches={colMatches}
                    direction={Direction.TopRight}
                    numDirections={numDirections}
                    matchHeight={defaultBracketConstants.previewBracketHeight}
                    matchWidth={defaultBracketConstants.previewBracketWidth}
                /></BracketContext.Provider>
            })
        ]
    }


    return (
        <>
            <div className='wpbb-bracket' ref={bracketRef}>
                {rounds.length > 0 && buildRounds2(rounds)}
            </div>
        </>
    )
}

