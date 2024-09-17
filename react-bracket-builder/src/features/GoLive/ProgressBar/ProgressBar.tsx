import { ProgressBarStep } from './ProgressBarStep'

export const ProgressBar = (props: {
  maxSteps: number
  currentStep: number
  stepNames: string[]
  className?: string
}) => {
  const baseClasses = 'tw-flex tw-gap-15 tw-w-full tw-p-30'
  const classes = props.className
    ? `${baseClasses} ${props.className}`
    : baseClasses
  return (
    <div className={classes}>
      {Array.from({ length: props.maxSteps }).map((_, i) => {
        return (
          <ProgressBarStep
            key={i}
            step={i}
            currentStep={props.currentStep}
            maxSteps={props.maxSteps}
            stepName={props.stepNames[i]}
          />
        )
      })}
    </div>
  )
}
