import React, { useEffect, useState } from 'react'
import { NumTeamsPicker } from './NumTeamsPicker'
import { MatchTree } from '../../shared/models/MatchTree'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import { BracketPreview } from './BracketPreview'
import { isPowerOfTwo } from '../../shared/components/Bracket/utils'
import { WildcardPicker } from './WildcardPicker'
import { ActionButton } from '../../shared/components/ActionButtons'
import { BracketMeta } from '../../shared/context/context'
import { WildcardPlacement } from '../../shared/models/WildcardPlacement'
import { BracketTitle } from './BracketTitle'

export interface NumTeamsPickerState {
  currentValue: number
  selected: boolean
}
interface NumTeamsPageProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  onAddTeamsClick: () => void
  numTeams: number
  setNumTeams: (numTeams: number) => void
  teamPickerDefaults: number[]
  teamPickerMin: number[]
  teamPickerMax: number[]
  wildcardPlacement: WildcardPlacement
  setWildcardPlacement: (placement: WildcardPlacement) => void
  teamPickerState: NumTeamsPickerState[]
  setTeamPickerState: (state: NumTeamsPickerState[]) => void
  bracketMeta: BracketMeta
  setBracketMeta?: (bracketMeta: BracketMeta) => void
}

export const NumTeamsPage = (props: NumTeamsPageProps) => {
  const {
    matchTree,
    setMatchTree,
    onAddTeamsClick,
    bracketMeta,
    setBracketMeta,
    numTeams,
    setNumTeams,
    teamPickerDefaults,
    teamPickerMin,
    teamPickerMax,
    wildcardPlacement,
    setWildcardPlacement,
    teamPickerState,
    setTeamPickerState,
  } = props

  const [showTitleError, setShowTitleError] = useState(false)

  // Update the global `numTeams` variable whenever picker state changes
  useEffect(() => {
    const picker = teamPickerState.find((picker) => picker.selected)
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
      currentValue: picker.currentValue + value,
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
      currentValue: value,
    }
    updateTeamPicker(index, newPicker)
  }

  const setTeamPickerSelected = (
    index: number,
    resetValue: boolean = false
  ) => {
    const newPickers = teamPickerState.map((picker, i) => {
      if (i === index) {
        return {
          ...picker,
          currentValue: resetValue
            ? teamPickerDefaults[index]
            : picker.currentValue,
          selected: true,
        }
      }
      return {
        ...picker,
        selected: false,
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

  const handleAddTeamsClick = () => {
    if (!bracketMeta.title) {
      setShowTitleError(true)
      // scroll to top of page
      window.scrollTo(0, 0)
    } else {
      onAddTeamsClick()
    }
  }

  const onTitleChange = (event: any) => {
    setShowTitleError(false)
  }

  return (
    <div
      className="tw-bg-no-repeat tw-bg-top tw-bg-cover"
      style={{ backgroundImage: `url(${darkBracketBg})` }}
    >
      <div className="tw-flex tw-flex-col tw-gap-40 tw-pb-[240px] tw-pt-60 tw-max-w-screen-lg tw-m-auto tw-px-20 lg:tw-px-0">
        <BracketTitle
          title={bracketMeta.title}
          placeholder="Name Your Bracket"
          setTitle={(title) => setBracketMeta({ ...bracketMeta, title })}
          onChange={onTitleChange}
          showError={showTitleError}
          errorMessage="Bracket name is required."
        />
        {matchTree && (
          <div>
            <BracketPreview matchTree={matchTree} />
          </div>
        )}
        <div
          className={`tw-flex tw-flex-col tw-gap-24 ${
            showWildCardOptions ? '' : 'tw-pb-24'
          }`}
        >
          <span className="tw-text-white/50 tw-text-center tw-font-500 tw-text-16 md:tw-text-24">
            How Many Teams in Your Bracket?
          </span>
          <div className="tw-flex tw-flex-col md:tw-flex-row tw-gap-24">
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
                  key={i}
                />
              )
            })}
          </div>
          {showWildCardOptions && (
            <WildcardPicker
              wildcardPlacement={wildcardPlacement}
              onWildcardPlacementChanged={handleWildcardPlacement}
            />
          )}
        </div>
        {/* <button className='tw-rounded-8 tw-border tw-border-solid tw-border-green tw-bg-green/15 tw-p-16 tw-flex tw-justify-center tw-cursor-pointer' onClick={onAddTeamsClick}>
          <span className='tw-text-white tw-font-500 tw-text-20 tw-uppercase tw-font-sans '>Add Your Teams</span>
        </button> */}
        <ActionButton variant="green" onClick={handleAddTeamsClick}>
          <span className="tw-text-white tw-font-500 tw-text-20 tw-uppercase tw-font-sans">
            Add Your Teams
          </span>
        </ActionButton>
      </div>
    </div>
  )
}
