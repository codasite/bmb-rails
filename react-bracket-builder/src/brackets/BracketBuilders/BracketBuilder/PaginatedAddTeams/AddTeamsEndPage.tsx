// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useContext } from 'react'
import { EndPageProps } from '../../PaginatedBuilderBase/types'
import { ActionButton } from '../../../shared/components/ActionButtons'
import { ScaledBracket } from '../../../shared/components/Bracket/ScaledBracket'
import { AddTeamsBracket } from '../../../shared/components/Bracket/AddTeamsBracket'
import { BracketMetaContext } from '../../../shared/context/context'
import { WindowDimensionsContext } from '../../../shared/context/WindowDimensionsContext'
import { DefaultEditButton } from '../../../shared/components/Bracket/BracketActionButtons'
import { ReactComponent as SaveIcon } from '../../../shared/assets/save.svg'

export const AddTeamsEndPage = (props: EndPageProps) => {
  const { matchTree, processing, handleSubmit, onEditClick } = props
  const { bracketMeta } = useContext(BracketMetaContext)
  const { title } = bracketMeta
  const { height: windowHeight, width: windowWidth } = useContext(
    WindowDimensionsContext
  )

  return (
    <div className="tw-flex tw-flex-col tw-items-center tw-gap-40">
      <h1 className="tw-text-white tw-font-700 tw-text-32 tw-text-center">
        {title}
      </h1>
      {matchTree && (
        <div className="tw-self-center">
          <ScaledBracket
            BracketComponent={AddTeamsBracket}
            matchTree={matchTree}
            windowWidth={windowWidth}
            windowHeight={windowHeight}
            paddingX={20}
          />
        </div>
      )}
      <div className="tw-flex tw-flex-col tw-gap-10">
        {onEditClick && (
          <DefaultEditButton onClick={onEditClick} disabled={processing} />
        )}
        <ActionButton
          variant="green"
          gap={16}
          onClick={handleSubmit}
          disabled={processing || !matchTree?.allTeamsAdded()}
        >
          <SaveIcon />
          <span className="tw-font-500 tw-text-20 tw-uppercase tw-font-sans">
            Save
          </span>
        </ActionButton>
      </div>
    </div>
  )
}
