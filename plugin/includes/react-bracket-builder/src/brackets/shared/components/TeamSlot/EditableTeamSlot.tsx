import React, { useState, useContext } from 'react'
//@ts-ignore
import { TeamSlotProps } from '../types'
import { InactiveTeamSlot } from './InactiveTeamSlot'
import { ActiveTeamSlot } from './ActiveTeamSlot'
import { BaseTeamSlot } from './BaseTeamSlot'
import { Team } from '../../models/Team'
import { getTeamFontSize } from '../Bracket/utils'
import { BufferedTextInput } from '../BufferedTextInput'
import { useResizeObserver } from '../../../../utils/hooks'

export const EditableTeamSlot = (props: TeamSlotProps) => {
  const {
    team,
    match,
    teamPosition,
    matchTree,
    setMatchTree,
    getFontSize = getTeamFontSize,
    width: boxWidth,
    textPaddingX = 4,
  } = props
  const borderWidth = 2
  const targetWidth = boxWidth - 2 * textPaddingX - 2 * borderWidth
  const fontSize = getFontSize(matchTree.rounds.length)
  const minFontSize = 10.5

  const [editing, setEditing] = useState(false)
  const [stopEditing, setStopEditing] = useState(false)
  const [shadowContent, setShadowContent] = useState('')

  const [inputWidth, setInputWidth] = useState(targetWidth)
  const [scale, setScale] = useState(1)
  const inputRef = React.useRef(null)
  const spanRef = React.useRef(null)

  const resizeCallback = React.useCallback(
    ({ width: currentWidth }) => {
      let scaleFactor = 1
      if (currentWidth > targetWidth) {
        scaleFactor = targetWidth / currentWidth
        setInputWidth(currentWidth)
        const currentHeight = fontSize * scaleFactor
        console.log('currentHeight', currentHeight)
        if (currentHeight < minFontSize) {
          setStopEditing(true)
        } else if (stopEditing) {
          setStopEditing(false)
        }
      }
      setScale(scaleFactor)
    },
    [shadowContent]
  )

  useResizeObserver(spanRef, resizeCallback)

  const doneEditing = (value: string) => {
    setEditing(false)
    if (!setMatchTree) {
      return
    }
    if (team) {
      team.name = value
    } else {
      const newTeam = new Team(value)
      match?.setTeam(newTeam, teamPosition === 'left')
    }

    setMatchTree(matchTree)
  }

  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    console.log('handleChange')
    const { value } = event.target
    setShadowContent(value)
  }

  return (
    <BaseTeamSlot
      {...props}
      backgroundColor={team?.name && !editing ? 'transparent' : 'white/15'}
      borderColor="white/50"
      textColor="white"
      borderWidth={borderWidth}
      teamClickDisabled={() => true}
    >
      <div className="tw-relative">
        <span
          ref={spanRef}
          className="tw-absolute tw-invisible tw-leading-none"
          style={{ fontSize: fontSize }}
        >
          {shadowContent}
        </span>
        <BufferedTextInput
          noMoreInput={stopEditing}
          inputRef={inputRef}
          initialValue={team ? team.name : ''}
          onChange={handleChange}
          onDoneEditing={doneEditing}
          placeholderEl={<span>Add Team</span>}
          className="tw-border-none tw-outline-none tw-text-white tw-bg-transparent tw-font-sans tw-uppercase tw-font-500 tw-text-center tw-p-0"
          style={{
            transform: `scale(${scale})`,
            width: inputWidth,
            fontSize: fontSize,
          }}
        />
      </div>
    </BaseTeamSlot>
  )
}
