// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useContext } from 'react'
import { StartPageProps } from '../../PaginatedBuilderBase/types'
import { BracketMetaContext } from '../../../shared/context/context'
import { ScaledBracket } from '../../../shared/components/Bracket/ScaledBracket'
import { AddTeamsBracket } from '../../../shared/components/Bracket'
import { ReadonlyTitleComponent } from '../../../shared/components/MatchBox/Children/ReadonlyTitleComponent'
import { DefaultEditButton } from '../../../shared/components/Bracket/BracketActionButtons'

export const AddTeamsStartPage = (props: StartPageProps) => {
  const { onStart } = props
  const { bracketMeta } = useContext(BracketMetaContext)
  const { title } = bracketMeta
  const { matchTree } = props
  return (
    <div className="tw-flex tw-flex-col tw-items-center tw-gap-40">
      <h1 className="tw-text-white tw-font-700 tw-text-32 tw-text-center">
        {title}
      </h1>
      {matchTree && (
        <ScaledBracket
          BracketComponent={AddTeamsBracket}
          matchTree={matchTree}
          TitleComponent={ReadonlyTitleComponent}
        />
      )}
      <DefaultEditButton onClick={onStart} borderWidth={0} />
    </div>
  )
}
