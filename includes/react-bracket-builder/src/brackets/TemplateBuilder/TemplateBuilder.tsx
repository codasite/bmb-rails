import React, { useState, useEffect } from 'react'
import './template-builder.scss'
import { NumTeamsPicker } from './NumTeamsPicker'
import { MatchTree, WildcardPlacement } from '../shared/models/MatchTree'
//@ts-ignore
import darkBracketBg from '../shared/assets/bracket-bg-dark.png'
import { AddTeamsPage } from './AddTeamsPage'
import { BracketTemplatePreview } from './BracketTemplatePreview'
import { isPowerOfTwo } from '../shared/utils'
import { WildcardPicker } from './WildcardPicker'



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
    <div className='tw-flex tw-justify-center tw-border-b-solid tw-border-white/30 tw-p-16 ' onClick={startEditing}>
      {editing ?
        <input
          className='tw-py-0 tw-outline-none tw-border-none tw-bg-transparent tw-text-32 tw-text-white tw-text-center tw-font-sans tw-w-full tw-uppercase'
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
        <h1 className='tw-font-500 tw-text-32 !tw-text-white/20 tw-text-center'>{title}</h1>
      }
    </div>
  )
}

const defaultBracketName = "MY BRACKET NAME"

const teamPickerDefaults = [16, 32, 64]
const teamPickerMin = [1, 17, 33]
const teamPickerMax = [31, 63, 64]

interface NumTeamsPickerState {
  currentValue: number
  selected: boolean
}

interface NumTeamsPageProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  onAddTeamsClick: () => void
}

const NumTeamsPage = (props: NumTeamsPageProps) => {
  const {
    matchTree,
    setMatchTree,
    onAddTeamsClick,
  } = props

  const initialPickerIndex = 0
  const [numTeams, setNumTeams] = useState(teamPickerDefaults[initialPickerIndex])
  const [teamPickerState, setTeamPickerState] = useState<NumTeamsPickerState[]>(
    teamPickerDefaults.map((val, i) => ({
      currentValue: val,
      selected: i === initialPickerIndex
    }))
  )
  const [bracketTitle, setBracketTitle] = useState(defaultBracketName);
  const [wildcardPlacement, setWildcardPlacement] = useState(WildcardPlacement.Split)

  // Update the global `numTeams` variable whenever picker state changes
  useEffect(() => {
    const picker = teamPickerState.find(picker => picker.selected)
    if (picker) {
      const numTeams = picker.currentValue
      setNumTeams(numTeams)
      if (setMatchTree) {
        setMatchTree(MatchTree.fromNumTeams(numTeams, wildcardPlacement))
      }
    }
  }, [teamPickerState, wildcardPlacement])


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
    updateTeamPickerValueBy(index, 1)
  }

  const decrementTeamPicker = (index: number) => {
    updateTeamPickerValueBy(index, -1)
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


  // Show wild card options if numTeams is a power of 2
  const showWildCardOptions = !isPowerOfTwo(numTeams)

  const handleWildcardPlacement = (placement: WildcardPlacement) => {
    setWildcardPlacement(placement)
  }

  // const handleShuffle = () => {
  //   let currentTreeData = matchTree.rounds;
  //   let size = currentTreeData.length - 1;
  //   let shuffledData = ShuffleTeam.getTeamNames(currentTreeData, size, bracketFields?.totalWildCardGames);
  //   let matchValue = ShuffleTeam.updateMatchTree(shuffledData, currentTreeData, size, bracketFields?.totalWildCardGames);
  //   setMatchTree(MatchTree.fromRounds(matchValue))
  // }

  // const handleSave = () => {
  //   setIsLoading(true)
  //   const req = matchTree.toRequest(bracketFields?.bracketTitle, true, bracketFields?.totalRounds, bracketFields?.totalWildCardGames, bracketFields?.wildCardPos)
  //   bracketApi.createBracket(req)
  //     .then((bracket) => {
  //       setIsLoading(false)
  //       window.location.href = '/wp-admin/edit.php?post_type=bracket';
  //     })
  //     .catch((error) => {
  //       MatchTree.fromOptions(4, 0, 1)
  //       setIsLoading(false)
  //     });
  // }
  // const handleRedirect = () => {

  //   setShowComponent(false)
  // }

  return (
    <div className="tw-flex tw-flex-col tw-gap-40 tw-pb-[240px] tw-pt-60 tw-max-w-screen-lg tw-m-auto tw-px-20 lg:tw-px-0">
      <BracketTitle title={bracketTitle} setTitle={setBracketTitle} />
      {
        matchTree &&
        <div>
          <BracketTemplatePreview matchTree={matchTree} />
        </div>
      }
      <div className={`tw-flex tw-flex-col tw-gap-24${showWildCardOptions ? '' : ' tw-pb-24'}`}>
        <span className='tw-text-white/50 tw-text-center tw-font-500 tw-text-24'>
          How Many total teams in Your Bracket
        </span>
        <div className='tw-flex tw-flex-col md:tw-flex-row tw-gap-24'>
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
        {
          showWildCardOptions &&
          <WildcardPicker
            wildcardPlacement={wildcardPlacement}
            onWildcardPlacementChanged={handleWildcardPlacement}
          />
        }
      </div>
      <button className='tw-rounded-8 tw-border tw-border-solid tw-border-green tw-bg-green/15 tw-p-16 tw-flex tw-justify-center tw-cursor-pointer' onClick={onAddTeamsClick}>
        <span className='tw-text-white tw-font-500 tw-text-20 tw-uppercase tw-font-sans '>Add Your Teams</span>
      </button>
    </div>
  )
}

interface TemplateBuilderProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  saveTemplateLink?: string
  saveTournamentLink?: string
}

const TemplateBuilder = (props: TemplateBuilderProps) => {
  const {
    matchTree,
    setMatchTree,
    saveTemplateLink,
    saveTournamentLink
  } = props

  const [currentPage, setCurrentPage] = useState('num-teams')

  const onAddTeamsClick = () => {
    setCurrentPage('add-teams')
  }

  return (
    <div className='wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover' style={{ 'backgroundImage': `url(${darkBracketBg})` }}>
      {currentPage === 'num-teams' &&
        <NumTeamsPage matchTree={matchTree} setMatchTree={setMatchTree} onAddTeamsClick={onAddTeamsClick} />
      }
      {currentPage === 'add-teams' &&
        <AddTeamsPage matchTree={matchTree} setMatchTree={setMatchTree} />
      }
    </div>
  )
}

export default TemplateBuilder