import React, { useState, useEffect } from 'react'
import Container from 'react-bootstrap/Container'
import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Button from 'react-bootstrap/Button'
import ButtonGroup from 'react-bootstrap/ButtonGroup'
import './user-template-builder.scss'
import { set } from 'immer/dist/internal'
import { NumTeamsPicker } from './NumTeamsPicker/NumTeamsPicker'
import { MatchTree } from '../shared/models/MatchTree'
import { Bracket } from '../shared/components/Bracket'
import { bracketApi } from '../shared/api/bracketApi';
import { ShuffleTeam } from './ShuffleTeam'
import { bracketConstants } from '../shared/constants'
import UserBracket from '../UserBracketBuilder/UserBracket/UserBracket'
import { BracketRes } from '../shared/api/types/bracket'
import { UserTemplatePreview } from './UserTemplatePreview/UserTemplatePreview'
import { PairedBracket } from '../UserBracketBuilder/UserBracket/components/PairedBracket'
//@ts-ignore
import { ReactComponent as ArrowNarrowLeft } from '../shared/assets/arrow-narrow-left.svg'
//@ts-ignore
import { ReactComponent as SaveIcon } from '../shared/assets/save-icon.svg'
//@ts-ignore
import { ReactComponent as PlayIcon } from '../shared/assets/play-icon.svg'
//@ts-ignore
import { ReactComponent as ShuffleIcon } from '../shared/assets/shuffle-icon.svg'
import Loader from '../shared/components/Loader/Loader'
import { DarkModeContext } from '../shared/context'


const defaultBracketName = "MY BRACKET NAME"
const WildCardPlacements = ['TOP', 'BOTTOM', 'CENTER', 'SPLIT']

const teamPickerDefaults = [16, 32, 64]
const teamPickerMin = [2, 18, 34]
const teamPickerMax = [16, 32, 64]

let bracketRes: any;

interface NumTeamsPickerState {
    currentValue: number
    selected: boolean
}

export interface BracketFields {
    bracketTitle: string,
    totalRounds: number,
    totalWildCardGames: number,
    wildCardPos: number
}

const BracketTitle = (props) => {
    const {
        title,
        setTitle
    } = props

    const [editing, setEditing] = useState(false)
    const [textBuffer, setTextBuffer] = useState(title)

    const startEditing = () => {
        setEditing(true)
        setTextBuffer(title)
    }
    const doneEditing = (event: any) => {
        setTitle(textBuffer)
        setEditing(false)
    }

    return (
        <div className='wpbb-bracket-title' onClick={startEditing}>
            {editing ?
                <input
                    className='wpbb-bracket-title-input'
                    autoFocus
                    onFocus={(e) => e.target.select()}
                    type='text'
                    value={textBuffer}
                    onChange={(e) => setTextBuffer(e.target.value)}
                    onBlur={doneEditing}
                    onKeyUp={(e) => {
                        if (e.key === 'Enter') {
                            doneEditing(e)
                        }
                    }}
                />
                :
                <div className='wpbb-bracket-title-name'>{title}</div>
            }
        </div>
    )
}

const evaluateNumRoundAndWildCard = (numTeams: number) => {
    let numRounds = 1
    let wildCardGame = 0
    while (numTeams > Math.pow(2, numRounds)) {
        numRounds++;
    }

    wildCardGame = numTeams - Math.pow(2, numRounds - 1);
    if (wildCardGame === Math.pow(2, numRounds - 1)) {
        wildCardGame = 0
    }

    return [
        numRounds,
        wildCardGame
    ];
}

const UserTemplateBuilder = () => {

    const initialPickerIndex = 1
    const [numTeams, setNumTeams] = useState(teamPickerDefaults[initialPickerIndex])
    const [teamPickerState, setTeamPickerState] = useState<NumTeamsPickerState[]>(
        teamPickerDefaults.map((val, i) => ({
            currentValue: val,
            selected: i === initialPickerIndex
        }))
    )
    const [bracketTitle, setBracketTitle] = useState(defaultBracketName);
    const [wildCardPos, setWildCardPos] = useState(initialPickerIndex)
    const [matchTree, setMatchTree] = useState<MatchTree>(MatchTree.fromOptions(4, 0, initialPickerIndex));
    const [totalRounds, setTotalRounds] = useState(Number);
    const [totalWildCardGames, setTotalWildCardGames] = useState(Number);
    const [showComponent, setShowComponent] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [bracketFields, setBracketFields] = useState<BracketFields>({
        bracketTitle: bracketTitle,
        totalRounds: 4,
        totalWildCardGames: 0,
        wildCardPos: initialPickerIndex
    }
    )
    // Update the global `numTeams` variable whenever picker state changes
    useEffect(() => {
        const picker = teamPickerState.find(picker => picker.selected)
        if (picker) {
            setNumTeams(picker.currentValue)
            const [totalRounds, totalWildCardGames] = evaluateNumRoundAndWildCard(picker.currentValue)
            setTotalRounds(totalRounds);
            setTotalWildCardGames(totalWildCardGames);
            setMatchTree(MatchTree.fromOptions(totalRounds, totalWildCardGames, wildCardPos))
        }
    }, [teamPickerState, wildCardPos])

    const handleWildCardPlacement = (index) => {
        setWildCardPos(index)
    }

    const CreateWildCardPlacementButtons = (props) => {
        const selected = props.wildCard();
        return (
            <div>
                <ButtonGroup aria-label="Basic example">
                    <Button className={`btn-secondary no-highlight-button pos-btn ${selected ? 'selected-btn' : ''}`} variant='secondary' onClick={() => handleWildCardPlacement(props.positionIndex)}>{props.position}</Button>
                </ButtonGroup>
            </div>
        )
    }

    const updateTeamPicker = (index: number, newPicker: NumTeamsPickerState) => {
        const newPickers = teamPickerState.map((picker, i) => {
            if (i === index) {
                return newPicker
            }
            return picker
        })
        setTeamPickerState(newPickers)
    }

    const updateTeamPickerValueBy = (index: number, value: number) => {
        const picker = teamPickerState[index]
        const newPicker = {
            ...picker,
            currentValue: picker.currentValue + value
        }
        updateTeamPicker(index, newPicker)
    }

    const incrementTeamPicker = (index: number) => {
        updateTeamPickerValueBy(index, 2)
    }

    const decrementTeamPicker = (index: number) => {
        updateTeamPickerValueBy(index, -2)
    }

    const setTeamPickerValue = (index: number, value: number) => {
        const picker = teamPickerState[index]
        const newPicker = {
            ...picker,
            currentValue: value
        }
        updateTeamPicker(index, newPicker)
    }

    const setTeamPickerSelected = (index: number, resetValue: boolean = false) => {
        const newPickers = teamPickerState.map((picker, i) => {
            if (i === index) {
                return {
                    ...picker,
                    currentValue: resetValue ? teamPickerDefaults[index] : picker.currentValue,
                    selected: true
                }
            }
            return {
                ...picker,
                selected: false
            }
        })
        setTeamPickerState(newPickers)
    }

    const setWildCardSelected = (index) => {
        if (wildCardPos === index) {
            return true;
        }
    }

    /**
     * Get the function to select the next team picker
     * When the next team picker is selected, its value is reset to the default value
     */
    const getSelectNextTeamPicker = (index: number) => {
        if (index < teamPickerState.length - 1) {
            return () => setTeamPickerSelected(index + 1, true)
        }
        return undefined
    }

    /**
     * Get the function to select the previous team picker
     * When the previous team picker is selected, its value is reset to the default value
     */
    const getSelectPrevTeamPicker = (index: number) => {
        if (index > 0) {
            return () => setTeamPickerSelected(index - 1, true)
        }
        return undefined
    }

    /**
     * Bitwise operation to check if a number is a power of 2
     */
    const isPowerOfTwo = (num: number) => {
        return (num & (num - 1)) === 0
    }

    // Show wild card options if numTeams is a power of 2
    const showWildCardOptions = !isPowerOfTwo(numTeams)

    const handleTeams = () => {
        setBracketFields({
            bracketTitle: bracketTitle,
            totalRounds: totalRounds,
            totalWildCardGames: totalWildCardGames,
            wildCardPos: wildCardPos
        }
        )
        bracketRes = matchTree;
        setShowComponent(true);
    }

    const handleShuffle = () => {
        let currentTreeData = matchTree.rounds;
        let size = currentTreeData.length - 1;
        let shuffledData = ShuffleTeam.getTeamNames(currentTreeData, size, bracketFields?.totalWildCardGames);
        let matchValue = ShuffleTeam.updateMatchTree(shuffledData, currentTreeData, size, bracketFields?.totalWildCardGames);
        setMatchTree(MatchTree.fromRounds(matchValue))
    }

    const handleSave = () => {
        setIsLoading(true)
        const req = matchTree.toRequest(bracketFields?.bracketTitle, true, bracketFields?.totalRounds, bracketFields?.totalWildCardGames, bracketFields?.wildCardPos)
        bracketApi.createBracket(req)
            .then((bracket) => {
                setIsLoading(false)
                window.location.href = '/wp-admin/edit.php?post_type=bracket';
            })
            .catch((error) => {
                MatchTree.fromOptions(4, 0, 1)
                setIsLoading(false)
            });
    }
    const handleRedirect = () => {

        setShowComponent(false)
    }

    const bracketProps = {
        matchTree,
        setMatchTree: (matchTree: MatchTree) => setMatchTree(matchTree),
        canPick: false,
        canEdit: true,
        bracketName: bracketRes?.name,
    }

    return (
        <div>
            {isLoading ? <Loader /> :
                <div className='wpbb-template-builder-root'>
                    {showComponent ? <div className="bracket-container">
                        <div>{<Button className='create-bracket' onClick={handleRedirect}><ArrowNarrowLeft />CREATE BRACKET</Button>}</div>
                        <div className='bracket-title'>{bracketTitle}</div>
                        <div className='paired-bracket'>
                            <DarkModeContext.Provider value={true}>
                                <PairedBracket {...bracketProps} />
                            </DarkModeContext.Provider>
                        </div>
                            <div className={`randomize-team-container wpbb-bracket-actions`}>
                                <Button className='randomize-teams no-highlight-button' onClick={handleShuffle} >
                                    <ShuffleIcon/>
                                    <span className={'randomize-teams-text'}>scramble team order</span>
                                </Button>
                            </div>
                            <div className='bracket-button'>
                                    <Button className='btn-save-bracket' variant='secondary' onClick={handleSave}>
                                        <SaveIcon/>
                                        <span className='save-bracket-text'>Save As Template</span>
                                    </Button>
                                    <Button className='btn-play-bracket' variant='secondary'>
                                        <PlayIcon/>
                                        <span className='play-bracket-text'>Create Tournament</span>
                                    </Button>
                            </div>
                    </div>
                        :
                        <div className="bracket-container">
                            <BracketTitle title={bracketTitle} setTitle={setBracketTitle} />
                            <div className='wpbb-default'>
                                <UserTemplatePreview matchTree={matchTree} />
                            </div>
                            <div className='team-picker-container'>
                                <div className='bracket-text-info'>
                                    How Many total teams in Your Bracket
                                </div>
                                <div className='team-picker'>
                                    {teamPickerState.map((pickerState, i) => {
                                        return (
                                            <NumTeamsPicker
                                                currentValue={pickerState.currentValue}
                                                defaultValue={teamPickerDefaults[i]}
                                                min={teamPickerMin[i]}
                                                max={teamPickerMax[i]}
                                                selected={pickerState.selected}
                                                setSelected={() => setTeamPickerSelected(i)}
                                                increment={() => incrementTeamPicker(i)}
                                                decrement={() => decrementTeamPicker(i)}
                                                setCurrentValue={(value) => setTeamPickerValue(i, value)}
                                                selectNextPicker={getSelectNextTeamPicker(i)}
                                                selectPrevPicker={getSelectPrevTeamPicker(i)}
                                            />
                                        )
                                    })}
                                </div>
                            </div>
                            <div className='wild-btn' style={{ display: showWildCardOptions ? 'block' : 'none' }}>
                                <div className='bracket-text-info  wild-card-display-text'>
                                    WILDCARD DISPLAY
                                </div>
                                <div className='wild-card-group'>
                                    {WildCardPlacements.map((pos, index) => (
                                        <div className='wild-card-btn' key={index}>
                                            <CreateWildCardPlacementButtons position={pos} positionIndex={index} wildCard={() => setWildCardSelected(index)} />
                                        </div>
                                    ))}
                                </div>
                            </div>
                            <Button className='btn-play-bracket' variant='secondary' onClick={handleTeams}>
                                <span className='play-bracket-text'>Add Your Teams</span>
                            </Button>
                        </div>
                    }
                </div>
            }
        </div>
    )
}

export default UserTemplateBuilder


