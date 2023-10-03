import * as React from 'react'
import { useState } from 'react'
import { bracketApi } from '../../../brackets/shared/api/bracketApi'
import { DashboardModal } from '../DashboardModal'

export const HostTournamentModal = (props: { tournamentsUrl: string }) => {
  const [templateId, setTemplateId] = useState<number | null>(null)
  const [loading, setLoading] = useState(false)
  const [input, setInput] = useState('')
  const [hasError, setHasError] = useState(false)
  const handleHostTournamentClick = (e: HTMLButtonElement) => {
    const templateId = e.dataset.templateId
    setTemplateId(parseInt(templateId))
  }
  const onHostTournament = () => {
    if (!input) {
      setHasError(true)
      return
    }
    setLoading(true)
    bracketApi
      .createTournament({
        bracketTemplateId: templateId,
        title: input,
      })
      .then((res) => {
        window.location.href = props.tournamentsUrl
      })
      .catch((err) => {
        console.error(err)
        setLoading(false)
      })
  }
  return (
    <DashboardModal
      submitButtonText={'Host'}
      onSubmit={onHostTournament}
      header={'Host tournament'}
      input={input}
      setInput={setInput}
      buttonClassName={'wpbb-host-tournament-button'}
      onButtonClick={handleHostTournamentClick}
      hasError={hasError}
      setHasError={setHasError}
      loading={loading}
      errorText={'Tournament name is required'}
      placeholderText={'Tournament name...'}
    />
  )
}
