import { useState } from 'react'
import { BracketRes } from '../../brackets/shared'
import { EntryFeePage } from './EntryFeePage'
import { SharePage } from './SharePage'
import { PageRouter } from './PageRouter'
import { Page } from './PageRouter/types'
import { GoLivePageProps } from './types'
import { TournamentTypePage } from './TournamentTypePage'

const GoLivePage = (props: GoLivePageProps) => {
  const [chargesEnabled, setChargesEnabled] = useState(false)
  const [bracket, setBracket] = useState<BracketRes>(props.bracket)
  const pages: Page[] = [
    {
      title: 'Tournament Type',
      slug: 'bracket-type',
      Component: TournamentTypePage,
    },
    { title: 'Entry Fee', slug: 'entry-fee', Component: EntryFeePage },
    { title: 'Share', slug: 'share', Component: SharePage },
  ]
  return (
    <PageRouter
      pages={pages}
      chargesEnabled={chargesEnabled}
      setChargesEnabled={setChargesEnabled}
      basePathPart="go-live"
      {...props}
      bracket={bracket}
      setBracket={setBracket}
    />
  )
}

export default GoLivePage
