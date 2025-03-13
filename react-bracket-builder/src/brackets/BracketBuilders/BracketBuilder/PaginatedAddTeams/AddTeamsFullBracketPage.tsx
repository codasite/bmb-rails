// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useContext } from 'react'
import { EndPageProps, StartPageProps } from '../../PaginatedBuilderBase/types'
import { BracketMetaContext } from '../../../shared/context/context'
import { ScaledBracket } from '../../../shared/components/Bracket/ScaledBracket'
import { AddTeamsBracket } from '../../../shared/components/Bracket'
import { ReadonlyTitleComponent } from '../../../shared/components/MatchBox/Children/ReadonlyTitleComponent'
import { DefaultEditButton } from '../../../shared/components/Bracket/BracketActionButtons'
import { EditableTitleComponent } from '../../../shared/components/MatchBox/Children/EditableTitleComponent'

export const AddTeamsFullBracketPage = (props: EndPageProps) => {
  const { onEditClick } = props
  const { bracketMeta, setBracketMeta } = useContext(BracketMetaContext)
  const { title } = bracketMeta
  const { matchTree } = props
  return (
    <div className="tw-flex tw-flex-col tw-items-center tw-gap-40 tw-max-w-full">
      <EditableTitleComponent
        title={title}
        fontSize={32}
        setTitle={(title) => {
          setBracketMeta({ ...bracketMeta, title })
        }}
      />
      {matchTree && (
        <ScaledBracket
          BracketComponent={AddTeamsBracket}
          matchTree={matchTree}
          TitleComponent={ReadonlyTitleComponent}
        />
      )}
      <DefaultEditButton onClick={onEditClick} borderWidth={0} />
    </div>
  )
}
