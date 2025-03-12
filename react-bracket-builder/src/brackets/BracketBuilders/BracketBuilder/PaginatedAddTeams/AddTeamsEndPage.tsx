// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useContext } from 'react'
import { EndPageProps } from '../../PaginatedBuilderBase/types'
import { ActionButton } from '../../../shared/components/ActionButtons'
import { ScaledBracket } from '../../../shared/components/Bracket/ScaledBracket'
import { AddTeamsBracket } from '../../../shared/components/Bracket/AddTeamsBracket'
import { BracketMetaContext } from '../../../shared/context/context'
import { WindowDimensionsContext } from '../../../shared/context/WindowDimensionsContext'
import { DefaultEditButton } from '../../../shared/components/Bracket/BracketActionButtons'

export const AddTeamsEndPage = (props: EndPageProps) => {
  const { matchTree, processing, handleSubmit, onEditClick } = props
  const { bracketMeta } = useContext(BracketMetaContext)
  const { title } = bracketMeta
  const { height: windowHeight, width: windowWidth } = useContext(
    WindowDimensionsContext
  )

  return (
    <div className="tw-flex tw-flex-col tw-justify-between tw-max-w-[1200px] tw-mx-auto tw-flex-grow tw-my-60">
      <h1 className="tw-text-white tw-font-700 tw-text-32 tw-text-center tw-mb-40">
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
      <div className="tw-flex tw-flex-col tw-gap-10 tw-mt-40">
        {onEditClick && (
          <DefaultEditButton onClick={onEditClick} disabled={processing} />
        )}
        <ActionButton
          variant="blue"
          onClick={handleSubmit}
          disabled={processing || !matchTree?.allTeamsAdded()}
        >
          Save Bracket
        </ActionButton>
      </div>
    </div>
  )
}
